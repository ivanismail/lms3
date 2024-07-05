<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingInviteResource\Pages;
use App\Filament\Resources\MeetingInviteResource\RelationManagers;
use App\Filament\Resources\ScheduleResource\RelationManagers\NotulensRelationManager;
use App\Models\InternalMember;
use App\Models\MeetingInvite;
use App\Models\Schedule;
use App\Utils\Utils;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MeetingInviteResource extends Resource
{
    protected static ?string $modelLabel = 'Undangan Meeting';
    protected static ?string $pluralModelLabel = 'Undangan Meeting';
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?int $navigationSort = 1;


    public static function canAccess(): bool
    {
        return Utils::isUser();
    }

    public static function canView(Model $record): bool
    {
        return static::can('view', $record);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->whereRelation('internalMembers', 'user_id', Auth::user()->id);
            })
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Meeting')
                    ->placeholder('Tidak ada kode')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Nama Meeting')
                    ->description(function ($record) {
                        return $record->description;
                        // return mb_strimwidth($record->description ?? '', 0, 30, "...");
                    })
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('room.name')
                    ->label('Ruangan')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->description(function ($record) {
                        return $record->room->location;
                        // return mb_strimwidth($record->description ?? '', 0, 30, "...");
                    })
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('start_at')
                    ->label('Waktu Mulai')
                    ->badge()
                    ->color('success')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable()
                    ->dateTime(),
                TextColumn::make('end_at')
                    ->badge()
                    ->color('danger')
                    ->label('Waktu Mulai')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable()
                    ->dateTime(),
                TextColumn::make('user.name')
                    ->label('Diundang Oleh')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('internalMember.status')
                    ->label('Status Kehadiran')
                    ->placeholder('Belum memutuskan')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'hadir' => 'success',
                            'absen' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'hadir' => 'Hadir',
                            'absen' => 'Tidak Hadir',
                            default => 'Belum Memutuskan',
                        };
                    }),
                TextColumn::make('internalMember.description')
                    ->label('Catatan')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('hadir')
                    ->label('Saya Hadir')
                    ->color('success')
                    ->button()
                    ->modalWidth(MaxWidth::Small)
                    ->visible(function ($record) {
                        return $record->internalMember->status == null;
                    })
                    ->form([
                        Textarea::make('description')
                            ->label('Tulis Catatan')
                            ->placeholder('Masukkan catatan tambahan'),
                    ])
                    ->action(function ($data, $record) {
                        $internalMember = InternalMember::query()
                            ->where([
                                'schedule_id' => $record->id,
                                'user_id' => Auth::user()->id,
                            ])
                            ->firstOrFail();

                        $internalMember->status = 'hadir';
                        $internalMember->description = $data['description'] ?? '';
                        $internalMember->save();
                    }),
                Action::make('absen')
                    ->label('Tidak Hadir')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->button()
                    ->modalWidth(MaxWidth::Small)
                    ->visible(function ($record) {
                        return $record->internalMember->status == null;
                    })
                    ->form([
                        Textarea::make('description')
                            ->label('Tulis Catatan')
                            ->placeholder('Masukkan catatan tambahan'),
                    ])
                    ->action(function ($data, $record) {
                        $internalMember = InternalMember::query()
                            ->where([
                                'schedule_id' => $record->id,
                                'user_id' => Auth::user()->id,
                            ])
                            ->firstOrFail();

                        $internalMember->status = 'absen';
                        $internalMember->description = $data['description'] ?? '';
                        $internalMember->save();
                    }),
                ViewAction::make()
                    ->visible(function ($record) {
                        return $record->internalMember->status == 'hadir';
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make([

                    Fieldset::make('Informasi Meeting')
                        ->schema([
                            TextEntry::make('name')
                                ->label('Nama'),
                            TextEntry::make('description')
                                ->label('Deskripsi')
                                ->placeholder('Tidak ada deskripsi'),
                            Split::make([
                                TextEntry::make('start_at')
                                    ->label('Waktu Mulai')
                                    ->formatStateUsing(function ($state) {
                                        return Utils::dateReadable($state);
                                    }),
                                TextEntry::make('end_at')
                                    ->label('Waktu Selesai')
                                    ->formatStateUsing(function ($state) {
                                        return Utils::dateReadable($state);
                                    }),
                            ])
                        ])->columns(1),
                    Fieldset::make('Informasi Ruangan')
                        ->schema([
                            TextEntry::make('room.name')
                                ->label('Ruangan'),
                            Split::make([
                                TextEntry::make('room.location')
                                    ->label('Lokasi'),
                                TextEntry::make('room.capacity')
                                    ->label('Kapasitas'),
                            ])
                        ])->columns(1)
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            NotulensRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeetingInvites::route('/'),
            'create' => Pages\CreateMeetingInvite::route('/create'),
            'view' => Pages\ViewMeetingInvite::route('/{record}'),
            'edit' => Pages\EditMeetingInvite::route('/{record}/edit'),
        ];
    }
}
