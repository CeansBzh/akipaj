<div class="overflow-hidden">
    <div id="slider" aria-label="Diaporama de photos d'utilisateurs">
        <div class="slide-track">
            @for($i = 1; $i < 11; $i++)
            <div class="slide">
                <img class="max-w-none rounded-md" src="{{ Vite::asset('resources/images/carousel/' . $i . '.webp') }}" alt="" />
            </div>
            @endfor
            @for($i = 1; $i < 11; $i++)
            <div class="slide">
                <img class="max-w-none rounded-md" src="{{ Vite::asset('resources/images/carousel/' . $i . '.webp') }}" alt="" />
            </div>
            @endfor
        </div>
    </div>
</div>

@push('styles')
<style>
    @keyframes scroll {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(calc(-310px * 10));
        }
    }

    #slider {
        position: relative;
        height: 400px;
        margin: auto;
    }

    #slider .slide-track {
        display: flex;
        animation: scroll 40s linear infinite;
        width: calc(250px * 3);
        height: 100%;
    }

    #slider .slide {
        height: auto;
    }

    #slider .slide img {
        object-fit: cover;
        height: 100%;
        width: 250px;
        margin: 0 30px;
        user-select: none;
    }

    @media (min-width: 768px) {

        #slider::before,
        #slider::after {
            background: linear-gradient(to right, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0) 100%);
            content: "";
            height: 100%;
            position: absolute;
            width: 200px;
            z-index: 2;
        }

        #slider::after {
            right: 0;
            top: 0;
            transform: rotateZ(180deg);
        }

        #slider::before {
            left: -1px;
            top: 0;
        }
    }
</style>
@endpush
