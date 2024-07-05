<?php

namespace App\Utils;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Hash;

class FilamentUtils
{
    public static function columnUpdatedAt(
        $verticallyAlignStart = false,
        $column = 'updated_at',
        $label = 'Terakhir diperbarui'
    ): TextColumn {
        return
            TextColumn::make($column)
            ->label($label)
            ->sortable()
            ->toggleable()
            ->verticallyAlignStart($verticallyAlignStart)
            ->formatStateUsing(function ($state) {
                return Utils::dateReadableShort($state);
            });
    }

    public static function columnCreatedAt(
        $verticallyAlignStart = false,
        $column = 'created_at',
        $label = 'Dibuat Pada'
    ): TextColumn {
        return
            TextColumn::make($column)
            ->label($label)
            ->sortable()
            ->toggleable()
            ->verticallyAlignStart($verticallyAlignStart)
            ->formatStateUsing(function ($state) {
                return Utils::dateReadableShort($state);
            });
    }

    public static function canChangePassword(
        bool $isLink = true,
    ) {
        $action = Action::make('change_password')
            ->color('info')
            ->label('Ubah Password')
            ->icon('heroicon-o-lock-closed')
            ->modalSubmitActionLabel('Ubah Password')
            ->modalHeading(function ($record) {
                return 'Ubah Password - ' . $record->name;
            })
            ->form([
                TextInput::make('password')->label('Password')
                    ->validationAttribute('Password')
                    ->required()
                    ->autocomplete(false)
                    ->confirmed()
                    ->password()
                    ->revealable()
                    ->minLength(8),
                TextInput::make('password_confirmation')->label('Konfirmasi Password')
                    ->validationAttribute('Konfirmasi Password')
                    ->required()
                    ->revealable()
                    ->autocomplete(false)
                    ->password(),
            ])
            ->action(function (array $data, $record): void {
                $user = $record;
                $user->password = Hash::make($data['password']);
                $user->save();

                Notification::make()
                    ->title('Password berhasil diubah')
                    ->success()
                    ->send();
            });

        if ($isLink) $action = $action
            ->link();

        return $action;
    }
}
