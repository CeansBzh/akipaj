<?php

namespace App\View\Components;

use App\Enums\AlertLevelEnum;
use Illuminate\View\Component;

class Alert extends Component
{
    /**
     * The color to use for the alert.
     *
     * @var string
     */
    public $color;

    /**
     * The message or an array of messages to present to the user
     *
     * @var mixed
     */
    public $message;

    /**
     * Unique identifier for the alert.
     *
     * @var string
     */
    public $id;

    /**
     * Create a new component instance.
     *
     * @param  string  $level
     * @param  mixed   $message
     */
    public function __construct(AlertLevelEnum $level = AlertLevelEnum::INFO, $message = '')
    {
       $this->color   = $level->color();
       $this->message = $message;
       $this->id      = uniqid();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.alert');
    }
}
