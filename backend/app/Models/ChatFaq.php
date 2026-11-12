<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatFaq extends Model
{
    protected $table = 'faq_chat';
    protected $fillable = ['pertanyaan', 'keywords', 'jawaban', 'urutan', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function keywordList(): array
    {
        return collect(explode(',', $this->keywords))
            ->map(fn ($k) => mb_strtolower(trim($k)))->filter()->values()->all();
    }
}
