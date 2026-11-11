<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Share;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pages ouvertes par un lien de partage, sans compte : une photo seule dans la visionneuse, ou les photos d'un album.
 * Un membre connecté qui ouvre le lien arrive dans la galerie, sur la photo ou l'album.
 */
class ShareController extends Controller
{
    /**
     * Nombre de photos d'un album partagé chargées à la fois.
     */
    public const PER_PAGE = 100;

    /**
     * En-têtes des pages et des fichiers partagés : ni moteur de recherche, ni jeton transmis aux autres sites.
     *
     * @var array<string, string>
     */
    protected const PRIVATE_HEADERS = [
        'X-Robots-Tag' => 'noindex, nofollow',
        'Referrer-Policy' => 'no-referrer',
    ];

    public function show(Request $request, Share $share): View|RedirectResponse|Response
    {
        $shareable = $this->usableShareable($share);

        if ($shareable === null) {
            return response()->view('share.expired', status: 410)->withHeaders(self::PRIVATE_HEADERS);
        }

        if ($request->user()?->can('view', $shareable)) {
            return $shareable instanceof Photo
                ? redirect()->route('photos.show', $shareable)
                : redirect()->route('photos.index', ['albums' => [$shareable->id]]);
        }

        if ($shareable instanceof Photo) {
            return response()->view('share.photo', [
                'share' => $share,
                'photo' => $shareable,
            ])->withHeaders(self::PRIVATE_HEADERS);
        }

        // « Connectez-vous » ramène un membre sur le lien, qui le mène alors à l'album dans la galerie
        redirect()->setIntendedUrl($request->url());

        $photos = $share->photos()->limit(self::PER_PAGE + 1)->get();

        return response()->view('share.album', [
            'share' => $share->load('user'),
            'album' => $shareable,
            'photoCount' => $shareable->photos()->count(),
            'photos' => $photos->take(self::PER_PAGE),
            'nextPageUrl' => $photos->count() > self::PER_PAGE ? route('shares.photos', [$share, 'page' => 2]) : null,
        ])->withHeaders(self::PRIVATE_HEADERS);
    }

    /**
     * Page suivante des vignettes d'un album partagé, ajoutée à la fin de la mosaïque par resources/js/shared-photos.js.
     */
    public function photos(Request $request, Share $share): Response
    {
        $album = $this->usableShareable($share);
        abort_unless($album instanceof Album, 404);

        $page = max(1, $request->integer('page', 1));
        $photos = $share->photos()->offset(($page - 1) * self::PER_PAGE)->limit(self::PER_PAGE + 1)->get();

        return response()->view('share.partials.photos', [
            'share' => $share,
            'photos' => $photos->take(self::PER_PAGE),
            'eagerCount' => 0,
        ])->withHeaders([
            ...self::PRIVATE_HEADERS,
            // Adresse de la page d'après, ou rien quand c'était la dernière
            'X-Next-Page' => $photos->count() > self::PER_PAGE ? route('shares.photos', [$share, 'page' => $page + 1]) : '',
        ]);
    }

    /**
     * Fichier d'une photo que le lien ouvre.
     */
    public function file(Share $share, Photo $photo): StreamedResponse
    {
        $this->ensureGrantsAccess($share, $photo);

        return Photo::fileResponse($photo->fileName(), self::PRIVATE_HEADERS);
    }

    /**
     * Vignette d'une photo que le lien ouvre.
     */
    public function thumbnail(Share $share, Photo $photo): StreamedResponse
    {
        $this->ensureGrantsAccess($share, $photo);

        return Photo::fileResponse($photo->thumbFileName(), self::PRIVATE_HEADERS);
    }

    /**
     * Photo ou album du lien, ou null si le lien a expiré ou si la photo ou l'album a été supprimé.
     */
    protected function usableShareable(Share $share): Photo|Album|null
    {
        if ($share->isExpired()) {
            return null;
        }

        $shareable = $share->shareable;

        return $shareable instanceof Photo || $shareable instanceof Album ? $shareable : null;
    }

    protected function ensureGrantsAccess(Share $share, Photo $photo): void
    {
        abort_if($this->usableShareable($share) === null || ! $share->grantsAccessTo($photo), 404);
    }
}
