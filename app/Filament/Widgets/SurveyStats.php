<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\Cache;

class SurveyStats extends BaseWidget
{
    protected static ?int $sort = 61;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('admin_dashboard_survey_stats', 120, function () {
            try {
                $totalSurveyQuestions = SurveyQuestion::count();
                $usersCompletedSurvey = SurveyResponse::distinct('user_id')->count();
                $totalSurveyResponses = SurveyResponse::count();
                
                // Get questions that are type 'skm' (Satisfaction Index) and 'spak' (other index)
                $skmQuestions = SurveyQuestion::where('type', 'skm')->count();
                $spakQuestions = SurveyQuestion::where('type', 'spak')->count();
                
                // For simplicity, we'll calculate based on responses. In a real app, you might have a specific table
                // for tracking who has or hasn't completed the survey
                $estimatedTotalUsers = \App\Models\User::count();
                $usersNotCompletedSurvey = max(0, $estimatedTotalUsers - $usersCompletedSurvey);

                return [
                    Stat::make('Total Pertanyaan Survey', $totalSurveyQuestions)
                        ->description('Pertanyaan dalam sistem')
                        ->descriptionIcon('heroicon-m-question-mark-circle')
                        ->color('info'),
                        
                    Stat::make('Pengguna Jawab Survey', $usersCompletedSurvey)
                        ->description('Pengguna Sudah Isi Survey')
                        ->descriptionIcon('heroicon-m-user')
                        ->color('success'),
                        
                    Stat::make('Belum Isi Survey', $usersNotCompletedSurvey)
                        ->description('Pengguna Belum Isi Survey')
                        ->descriptionIcon('heroicon-m-user-circle')
                        ->color('warning'),
                        
                    Stat::make('Total Respon Survey', $totalSurveyResponses)
                        ->description('Respon Survey Keseluruhan')
                        ->descriptionIcon('heroicon-m-document-text')
                        ->color('primary'),
                        
                    Stat::make('Pertanyaan SKM', $skmQuestions)
                        ->description('Pertanyaan SKM')
                        ->descriptionIcon('heroicon-m-academic-cap')
                        ->color('purple'),
                        
                    Stat::make('Pertanyaan SPAK', $spakQuestions)
                        ->description('Pertanyaan SPAK')
                        ->descriptionIcon('heroicon-m-shield-check')
                        ->color('emerald'),
                ];
            } catch (\Exception $e) {
                report($e);
                return [
                    Stat::make('Error', 'Database Error')
                        ->description('Could not load survey stats')
                        ->color('danger'),
                ];
            }
        });
    }
}