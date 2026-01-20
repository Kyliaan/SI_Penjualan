<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Models\Produk;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Products Information')
                ->schema([

                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('price')
                            ->numeric()
                            ->prefix('IDR')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        FileUpload::make('thumbnail')
                            ->image()
                            ->directory('produk/thumbnail')
                            ->required(),

                        Repeater::make('photos')
                            ->label('Product Photos')
                            ->schema([
                                FileUpload::make('photo')
                                    ->image()
                                    ->directory('produk/photos')
                                    ->required(),
                            ])
                            ->addActionLabel('Add Photo'),
                    ]),

                    Grid::make(2)->schema([
                        Repeater::make('sizes')
                            ->label('Product Sizes')
                            ->schema([
                                TextInput::make('size')->required(),
                            ]),
                    ]),

                    Section::make('Information Tambahan')
                        ->schema([

                            Grid::make(2)->schema([
                                TextInput::make('about')
                                    ->label('About Product')
                                    ->required(),

                                Select::make('is_popular')
                                    ->label('Popular Product')
                                    ->options([
                                        1 => 'Yes',
                                        0 => 'No',
                                    ])
                                    ->required(),
                            ]),

                            Grid::make(3)->schema([
                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required(),

                                Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->required(),

                                TextInput::make('stock')
                                    ->numeric()
                                    ->suffix('pcs')
                                    ->required(),
                            ]),
                        ]),
                ])->columns(1),
        ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                ImageColumn::make('thumbnail')->circular(),
                TextColumn::make('price')->money('IDR', true)->sortable(),
                TextColumn::make('category.name')->searchable(),
                TextColumn::make('brand.name')->searchable(),
                TextColumn::make('stock')->sortable(),
                IconColumn::make('is_popular')->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }
}
