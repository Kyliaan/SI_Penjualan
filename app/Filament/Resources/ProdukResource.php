<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Models\Produk;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdukResource extends Resource
{
    // Model utama
    protected static ?string $model = Produk::class;

    // Konfigurasi menu sidebar
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';

    // FORM
    public static function form(Form $form): Form
    {
        return $form->schema([

            // Fieldset data utama produk
            Fieldset::make('Details')
                ->schema([

                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('IDR'),

                    FileUpload::make('thumbnail')
                        ->image()
                        ->directory('produk/thumbnail')
                        ->required(),

                    Repeater::make('photos')
                        ->relationship()
                        ->schema([
                            FileUpload::make('photo')
                                ->image()
                                ->directory('produk/photos')
                                ->required(),
                        ])
                        ->addActionLabel('Add Photo'),

                    Repeater::make('sizes')
                        ->relationship()
                        ->schema([
                            TextInput::make('size')
                                ->required(),
                        ])
                        ->addActionLabel('Add Size'),
                ]),

            // Fieldset informasi tambahan
            Fieldset::make('Additional Information')
                ->schema([

                    TextInput::make('about')
                        ->required(),

                    Select::make('is_popular')
                        ->options([
                            1 => 'Popular',
                            0 => 'Not Popular',
                        ])
                        ->required(),

                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('brand_id')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('stock')
                        ->numeric()
                        ->required()
                        ->prefix('qty'),
                ]),
        ]);
    }

    // TABLE
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Category'),

                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->circular(),

                IconColumn::make('is_popular')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Popular'),
            ])

            // Filter
            ->filters([

                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name'),

                TrashedFilter::make(), //filter data terhapus
            ])

            // Aksi per baris
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),

                Tables\Actions\RestoreAction::make(), // restore
                Tables\Actions\ForceDeleteAction::make() // hapus permanen
        ->requiresConfirmation(),
            ])

            // Aksi massal
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(), // restore massal
                    Tables\Actions\ForceDeleteBulkAction::make(), // hapus permanen massal
                ]),
            ]);
    }

    // Query supaya bisa baca data soft delete
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // PAGES
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit'   => Pages\EditProduk::route('/{record}/edit'),
        ];
    }
}
