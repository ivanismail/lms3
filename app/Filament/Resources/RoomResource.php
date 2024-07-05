<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use App\Utils\Utils;
use Filament\Forms;
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

class RoomResource extends Resource
{
    protected static ?string $modelLabel = 'Ruangan';
    protected static ?string $pluralModelLabel = 'Ruangan';
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return Utils::isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->validationAttribute('Nama')
                    ->columnSpanFull()
                    ->required()
                    ->placeholder('Masukkan nama ruangan')
                    ->unique(
                        table: 'rooms',
                        column: 'name',
                        ignoreRecord: true,
                    ),
                Textarea::make('description')
                    ->label('Deskripsi Ruangan')
                    ->validationAttribute('Deskripsi Ruangan')
                    ->columnSpanFull()
                    ->placeholder('Ketik deskripsi ruangan'),
                TextInput::make('location')
                    ->label('Lokasi')
                    ->validationAttribute('Lokasi')
                    ->required()
                    ->placeholder('Masukkan lokasi ruangan'),
                TextInput::make('capacity')
                    ->label('Kapasitas')
                    ->validationAttribute('Kapasitas')
                    ->required()
                    ->numeric()
                    ->suffix('Orang')
                    ->placeholder('Masukkan kapasitas ruangan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->description(function ($record) {
                        return mb_strimwidth($record->description ?? '', 0, 30, "...");
                    })
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable()
                    ->formatStateUsing(function ($state) {
                        return $state . ' Orang';
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            // 'create' => Pages\CreateRoom::route('/create'),
            // 'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
