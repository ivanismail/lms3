<?php

namespace App\Filament\Resources\MeetingInviteResource\Pages;

use App\Filament\Resources\MeetingInviteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListMeetingInvites extends ListRecords
{
    protected static string $resource = MeetingInviteResource::class;

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
