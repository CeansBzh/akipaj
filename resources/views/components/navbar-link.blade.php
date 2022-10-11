<li>
    <a href="{{ $url }}" 
    @class([
    'block',
    'py-2',
    'pr-4',
    'pl-3',
    'rounded',
    'md:p-0',
    'text-white' => $isActive,
    'bg-blue-700' => $isActive,
    'md:bg-transparent' => $isActive,
    'md:text-blue-700' => $isActive,
    'md:p-0' => $isActive,
    'dark:text-white' => $isActive,
    'text-gray-700' => ! $isActive,
    'hover:bg-gray-100' => ! $isActive,
    'md:hover:bg-transparent' => ! $isActive,
    'md:hover:text-blue-700' => ! $isActive,
    'dark:text-gray-400' => ! $isActive,
    'md:dark:hover:text-white' => ! $isActive,
    'dark:hover:bg-gray-700' => ! $isActive,
    'dark:hover:text-white' => ! $isActive,
    'md:dark:hover:bg-transparent' => ! $isActive,
    'dark:border-gray-700' => ! $isActive,
    ]) 
    @if($isActive) aria-current="page" @endif
    >
        {{ $slot }}
    </a>
</li>