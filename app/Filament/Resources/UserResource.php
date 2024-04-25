<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Http;
use Filament\Tables\Actions\Button;
use Filament\Tables\Actions\Action;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('email')->required(),
                    Forms\Components\Hidden::make('password')->default('1'),
                    FileUpload::make('image')->image()
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->limit(20)->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->limit(50)->sortable()->searchable(),
                ImageColumn::make('image'),
                // Tables\Columns\ImageColumn::make('image')->image(function ($value, $record) {
                //     return Storage::disk('public')->url($value);
                // }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('Brevo Pushed')
                    ->color('success')
                    ->action(static function ($record) {
                        $response = $response = Http::withHeaders([
                            'api-key' => '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',
                            'Content-Type' => 'application/json',
                        ])->post('https://api.brevo.io/v3/contacts', [
                            // 'name' => $record->name,
                            'email' => $record->email,
                            // Add other relevant data
                        ]);

                        if ($response->successful()) {
                            // Integration successful
                            // return "SUCCESS";

                            return redirect()->back()->with('success', 'Pushed to Brevo successfully!');
                        }

                        // Integration failed
                        // return "FAILED";
                        return redirect()->back()->with('error', 'Failed to push to Brevo.');
                    })
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function getActions(): array
    {
        return [
            'push_to_brevo' => function ($record) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer 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',
                ])->post('https://api.brevo.io/v1/users/create', [
                    'data' => [
                        'name' => $record->name,
                        'email' => $record->email,
                        // Add other relevant data
                    ],
                ]);

                if ($response->successful()) {
                    // Integration successful
                    return redirect()->back()->with('success', 'Pushed to Brevo successfully!');
                }

                // Integration failed
                return redirect()->back()->with('error', 'Failed to push to Brevo.');
            },
        ];
    }
}
