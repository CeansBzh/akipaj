<?php

namespace App\Models;

use App\Casts\RichText;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'start_time',
        'end_time',
        'imagePath',
    ];

    protected $casts = [
        'description' => RichText::class,
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the description without its formatting, for the event cards and the calendar reminders.
     *
     * @return Attribute<string, never>
     */
    protected function descriptionText(): Attribute
    {
        return Attribute::get(fn (): string => RichText::toPlainText($this->description));
    }

    /**
     * Indique si l'événement a lieu aujourd'hui. Ses dates n'ont pas d'heure : il est en cours de son premier à son dernier jour inclus.
     */
    public function isOngoing(): bool
    {
        return today()->between($this->start_time->copy()->startOfDay(), $this->end_time->copy()->startOfDay());
    }
}
