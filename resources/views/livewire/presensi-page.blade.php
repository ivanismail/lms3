<div>
    <div
        class="h-16 fi-header-heading text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl text-center">
        Konfirmasi Kehadiran Meeting</div>

    <div class="mb-5">
        {{ $this->infolist }}
    </div>

    <form wire:submit="create">
        {{ $this->form }}

        <x-filament::button form="create" type="submit" class="w-full">
            Konfirmasi
        </x-filament::button>

    </form>

    <x-filament-actions::modals />

    {{-- <x-filament::avatar src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/User_icon_2.svg/1200px-User_icon_2.svg.png" alt="Dan Harrin" /> --}}
</div>
