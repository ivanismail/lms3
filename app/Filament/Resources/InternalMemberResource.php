<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InternalMemberResource\Pages;
use App\Filament\Resources\InternalMemberResource\RelationManagers;
use App\Models\InternalMember;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Utils\FilamentUtils;
use App\Utils\Utils;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class InternalMemberResource extends Resource
{
    protected static ?string $modelLabel = 'Member Internal';
    protected static ?string $pluralModelLabel = 'Member Internal';
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 2;

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
                    ->placeholder('Masukkan nama'),
                TextInput::make('email')
                    ->label('Email')
                    ->validationAttribute('Email')
                    ->columnSpanFull()
                    ->required()
                    ->placeholder('Masukkan email')
                    ->unique(
                        table: 'users',
                        column: 'email',
                        ignoreRecord: true,
                    ),
                TextInput::make('password')
                    ->label('Password')
                    ->validationAttribute('Password')
                    ->required()
                    ->autocomplete(false)
                    ->confirmed()
                    ->password()
                    ->minLength(8)
                    ->revealable()
                    ->hiddenOn('edit'),
                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->validationAttribute('Konfirmasi Password')
                    ->required()
                    ->autocomplete(false)
                    ->revealable()
                    ->password()
                    ->hiddenOn('edit'),
                Select::make('tag_id')
                    ->label('Tag')
                    ->columnSpanFull()
                    ->searchable()
                    ->preload()
                    ->optionsLimit(10)
                    ->createOptionUsing(function (array $data): int {
                        $tag = Tag::query()
                            ->where('name', $data['name'])
                            ->first();

                        if (!empty($tag))
                            return $tag->id;

                        $tag = new Tag();
                        $tag->name = $data['name'];
                        $tag->save();

                        return $tag->id;
                    })
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Tag')
                            ->minLength(3)
                            ->required()
                            ->columnSpanFull()
                            ->unique(
                                ignoreRecord: true,
                                column: 'name',
                                table: 'tags',
                                modifyRuleUsing: function (Unique $rule) {
                                    return $rule;
                                }
                            ),
                    ])
                    ->options(
                        function () {
                            return Tag::query()
                                ->latest()
                                ->pluck('name', 'id');
                        }
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->where('role_id', Role::USER);
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(
                        isIndividual: true,
                    )
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('tag.name')
                    ->label('Keterangan')
                    ->badge()
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
                FilamentUtils::canChangePassword(),
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
            'index' => Pages\ListInternalMembers::route('/'),
            // 'create' => Pages\CreateInternalMember::route('/create'),
            // 'edit' => Pages\EditInternalMember::route('/{record}/edit'),
        ];
    }
}
