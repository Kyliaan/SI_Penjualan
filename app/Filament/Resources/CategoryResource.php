<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    // Model yang digunakan
    protected static ?string $model = Category::class;

    // Icon sidebar
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Form tambah & edit kategori
    public static function form(Form $form): Form
    {
        return $form->schema([

            // Input nama kategori
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->unique(
                    table: Category::class,
                    column: 'name',
                    ignoreRecord: true
                ),

            // Upload icon kategori
            Forms\Components\FileUpload::make('icon')
                ->image()
                ->directory('categories')
                ->maxSize(1024)
                ->required(),
        ]);
    }

    // Tabel daftar kategori
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Kolom nama kategori
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                // Kolom icon kategori
                Tables\Columns\ImageColumn::make('icon')
                    ->circular(),
            ])

            // Filter data terhapus
            ->filters([
                TrashedFilter::make(),
            ])

            // Aksi per baris
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Category $record) => ! $record->trashed()),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn (Category $record) => ! $record->trashed()),

                Tables\Actions\RestoreAction::make()
                    ->visible(fn (Category $record) => $record->trashed()),

                Tables\Actions\ForceDeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn (Category $record) => $record->trashed()),
            ])

            // Aksi banyak data
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

    // Halaman resource
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
