<?php

namespace App\View\Components;

use Illuminate\View\Component;

class NavbarLink extends Component
{

    /**
     * The url to link to.
     *
     * @var string
     */
    public $url;


    /**
     * Whether the current link is the page the user is on or not.
     *
     * @var boolean
     */
    public $isActive;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->isActive = request()->url() == $url;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.navbar-link');
    }
}
