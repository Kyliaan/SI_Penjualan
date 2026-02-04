<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrandResource extends Resource
{
    // Model yang dipakai resource ini
    protected static ?string $model = Brand::class;

    // Icon di sidebar Filament
    protected static ?string $navigationIcon = 'heroicon-o-star';

    // Form untuk tambah & edit data
    public static function form(Form $form): Form
    {
        return $form->schema([

            // Input nama brand
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->unique(
                    table: Brand::class,
                    column: 'name',
                    ignoreRecord: true
                ),

            // Upload logo brand
            Forms\Components\FileUpload::make('logo')
                ->image()
                ->directory('brands')
                ->maxSize(1024)
                ->required(),
        ]);
    }

    // Tabel untuk menampilkan data
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Kolom nama brand
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                // Kolom logo brand
                Tables\Columns\ImageColumn::make('logo')
                    ->circular(),
            ])

            // Filter data terhapus
            ->filters([
                TrashedFilter::make(),
            ])

            // Aksi per baris
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),

                Tables\Actions\RestoreAction::make(), // restore data

                Tables\Actions\ForceDeleteAction::make() // hapus permanen
                    ->requiresConfirmation(),

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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
