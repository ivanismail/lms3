<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Filament\Resources\ScheduleResource\RelationManagers\ExternalMembersRelationManager;
use App\Filament\Resources\ScheduleResource\RelationManagers\InternalMembersRelationManager;
use App\Filament\Resources\ScheduleResource\RelationManagers\NotulensRelationManager;
use App\Models\Schedule;
use App\Utils\Utils;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ScheduleResource extends Resource
{
    protected static ?string $modelLabel = 'Jadwal Meeting';
    protected static ?string $pluralModelLabel = 'Jadwal Meeting';
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->label('Nama Meeting')
                        ->validationAttribute('Nama Meeting')
                        ->placeholder('Masukkan nama meeting')
                        ->columnSpanFull()
                        ->required(),
                    TextInput::make('code')
                        ->label('Kode Meeting')
                        ->validationAttribute('Kode Meeting')
                        ->placeholder('Masukkan kode meeting')
                        ->columnSpanFull()
                        ->unique(
                            table: 'schedules',
                            column: 'code',
                            ignoreRecord: true,
                        ),
                    Textarea::make('description')
                        ->label('Deskripsi Meeting')
                        ->validationAttribute('Deskripsi Meeting')
                        ->columnSpanFull()
                        ->placeholder('Ketik deskripsi meeting'),

                    Fieldset::make('Waktu Meeting')
                        ->schema([
                            Placeholder::make('start_at')
                                ->label('Mulai')
                                ->content(function ($state) {
                                    return Utils::dateReadable($state);
                                }),
                            Placeholder::make('end_at')
                                ->label('Selesai')
                                ->content(function ($state) {
                                    return Utils::dateReadable($state);
                                }),
                        ])
                        ->columns(2)
                        ->columnSpan(1),


                    Fieldset::make('Ruangan Meeting')
                        ->schema([
                            Placeholder::make('room.name')
                                ->label('Ruangan')
                                ->columnSpanFull()
                                ->content(function ($get, $record) {
                                    return $record->room->name;
                                }),
                            Placeholder::make('room.location')
                                ->label('Lokasi')
                                ->content(function ($get, $record) {
                                    return $record->room->location;
                                }),
                            Placeholder::make('room.capacity')
                                ->label('Kapasitas')
                                ->content(function ($get, $record) {
                                    return $record->room->capacity . ' Orang';
                                }),
                        ])
                        ->columns(2)
                        ->columnSpan(1),
                ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->where('user_id', Auth::user()->id);
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
                        return mb_strimwidth($record->description ?? '', 0, 30, "...");
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
                TextColumn::make('link')
                    ->label('Public Link')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->toggleable()
                    ->color('primary')
                    ->copyable()
                    ->copyableState(function ($record) {
                        return route('meeting-confirmation', [
                            'code' => $record->code,
                        ]);
                    })
                    ->formatStateUsing(function ($record) {
                        return route('meeting-confirmation', [
                            'code' => $record->code,
                        ]);
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            NotulensRelationManager::class,
            InternalMembersRelationManager::class,
            ExternalMembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            // 'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
