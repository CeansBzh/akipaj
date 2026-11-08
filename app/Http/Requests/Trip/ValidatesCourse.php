<?php

namespace App\Http\Requests\Trip;

use Illuminate\Support\Carbon;
use Throwable;

/**
 * Règles du parcours d'une sortie, communes à la création et à la modification.
 *
 * Le parcours arrive sous la clé `course`, en liste de points. Sans cette clé, le parcours enregistré ne change pas ;
 * une clé `course` vide le supprime.
 */
trait ValidatesCourse
{
    /**
     * @return array<string, mixed>
     */
    protected function courseRules(): array
    {
        return [
            'course' => 'nullable|array|max:100',
            'course.*.name' => 'required|string|max:100',
            'course.*.day' => ['required', 'integer', 'min:1', 'max:'.$this->tripDurationInDays()],
            'course.*.latitude' => 'required|numeric|between:-90,90',
            'course.*.longitude' => 'required|numeric|between:-180,180',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function courseMessages(): array
    {
        return [
            'course.max' => 'Le parcours ne peut pas compter plus de :max points.',
            'course.*.name.required' => 'Donnez un nom à chaque point du parcours.',
            'course.*.name.max' => 'Le nom d\'un point du parcours ne peut pas dépasser :max caractères.',
            'course.*.day.min' => 'Chaque point du parcours doit être rattaché à un jour de la sortie.',
            'course.*.day.max' => 'Un point du parcours est rattaché au jour :input, après la fin de la sortie : choisissez un jour entre 1 et :max.',
            'course.*.latitude.*' => 'Un point du parcours n\'a pas une position valide : replacez-le sur la carte.',
            'course.*.longitude.*' => 'Un point du parcours n\'a pas une position valide : replacez-le sur la carte.',
        ];
    }

    /**
     * Donne le nombre de jours de la sortie d'après les dates envoyées.
     * Quand elles manquent ou ne vont pas, leurs propres règles le signalent : le jour des points n'est alors pas borné.
     */
    private function tripDurationInDays(): int
    {
        $unbounded = 366;

        if (! is_string($this->input('start_date')) || ! is_string($this->input('end_date'))) {
            return $unbounded;
        }

        try {
            $start = Carbon::parse($this->input('start_date'))->startOfDay();
            $end = Carbon::parse($this->input('end_date'))->startOfDay();
        } catch (Throwable) {
            return $unbounded;
        }

        return $end->lt($start) ? $unbounded : (int) $start->diffInDays($end) + 1;
    }
}
