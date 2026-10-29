<?php

namespace App\Filament\Resources\Sites\Tables;

use App\Models\Site;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('domain')
                    ->label('Domain')
                    ->getStateUsing(fn ($record) => strtolower($record->slug) . '.' . config('app.site_base_domain', 'localhost'))
                    ->url(fn ($record) => 'http://' . strtolower($record->slug) . '.' . config('app.site_base_domain', 'localhost'))
                    ->openUrlInNewTab(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('files')
                    ->label('Files')
                    ->icon('heroicon-o-folder-open')
                    ->modalHeading('Site files')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(function (Site $record) {
                        $base = storage_path('app/private/' . $record->storage_path);
                        $files = [];

                        if (is_dir($base)) {
                            $iterator = new \RecursiveIteratorIterator(
                                new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS),
                            );

                            foreach ($iterator as $file) {
                                if ($file->isFile()) {
                                    $files[] = ltrim(str_replace($base, '', $file->getPathname()), '/');
                                }
                            }
                        }

                        return view('filament.site-files', ['site' => $record, 'files' => $files]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
