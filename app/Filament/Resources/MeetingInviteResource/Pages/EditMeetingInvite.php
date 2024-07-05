<?php

namespace App\Filament\Resources\MeetingInviteResource\Pages;

use App\Filament\Resources\MeetingInviteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeetingInvite extends EditRecord
{
    protected static string $resource = MeetingInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
