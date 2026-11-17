<?php

namespace App\Console\Commands;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Passe les vignettes des photos envoyées avant le WebP à ce format, plus léger, et crée les vignettes
 * des couvertures d'albums qui n'en ont pas encore. Peut être relancée : elle ne refait que ce qui manque.
 */
#[Signature('photos:thumbnails')]
#[Description('Crée les vignettes WebP des photos et les vignettes des couvertures d\'albums qui manquent')]
class CreateThumbnails extends Command
{
    public function handle(): int
    {
        $this->convertPhotoThumbnails();
        $this->createAlbumCoverThumbnails();

        return self::SUCCESS;
    }

    /**
     * Refait en WebP la vignette de chaque photo qui n'en a pas, à partir de la photo d'origine (ou de l'ancienne
     * vignette si l'origine manque), puis supprime l'ancienne vignette.
     */
    protected function convertPhotoThumbnails(): void
    {
        $extension = Photo::thumbnailExtension();

        if ($extension !== 'webp') {
            $this->warn('Le PHP de ce serveur ne sait pas écrire le WebP : les vignettes des photos restent en JPEG.');

            return;
        }

        $disk = Storage::disk('photos');
        $photos = Photo::where('thumb_path', 'not like', '%.webp');
        $converted = 0;
        $missing = 0;
        $bar = $this->output->createProgressBar($photos->count());

        $photos->chunkById(100, function ($chunk) use ($disk, $extension, &$converted, &$missing, $bar): void {
            foreach ($chunk as $photo) {
                $bar->advance();
                $oldThumb = $photo->thumbFileName();
                $source = $disk->fileExists($photo->fileName()) ? $photo->fileName() : $oldThumb;
                $image = $disk->fileExists($source) ? @imagecreatefromstring($disk->get($source)) : false;

                if ($image === false) {
                    $missing++;

                    continue;
                }

                $newThumb = 'thumbs/'.pathinfo($oldThumb, PATHINFO_FILENAME).'.'.$extension;
                [$photo->thumb_width, $photo->thumb_height] = Photo::writeThumbnail($image, $disk->path($newThumb));
                $photo->thumb_path = route('photos.file', $newThumb, false);
                $photo->saveQuietly();

                if ($oldThumb !== $newThumb) {
                    $disk->delete($oldThumb);
                }

                $converted++;
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Vignettes de photos passées en WebP : {$converted}.");

        if ($missing > 0) {
            $this->warn("Photos dont le fichier est introuvable, laissées telles quelles : {$missing}.");
        }
    }

    protected function createAlbumCoverThumbnails(): void
    {
        $created = 0;

        foreach (Album::whereNotNull('imagePath')->whereNull('cover_thumb_path')->get() as $album) {
            $album->refreshCoverThumbnail();

            if ($album->cover_thumb_path) {
                $album->saveQuietly();
                $created++;
            }
        }

        $this->info("Vignettes de couvertures d'albums créées : {$created}.");
    }
}
