<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ChatFaq;
use App\Models\ChatSetting;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function index()
    {
        $settings = [
            'bot_name' => ChatSetting::get('bot_name'),
            'greeting' => ChatSetting::get('greeting'),
            'is_active' => ChatSetting::get('is_active'),
            'ai_provider' => ChatSetting::get('ai_provider'),
            'ai_model' => ChatSetting::get('ai_model'),
            'ai_key_set' => ChatSetting::get('ai_api_key') !== '',
        ];
        $faqs = ChatFaq::orderBy('urutan')->orderBy('id')->paginate(10);
        return view('superadmin.chatbot.index', compact('settings', 'faqs'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'bot_name' => 'required|string|max:50',
            'greeting' => 'required|string|max:500',
            'ai_provider' => 'required|in:off,gemini',
            'ai_model' => 'nullable|string|max:100',
            'ai_api_key' => 'nullable|string|max:200',
        ]);
        ChatSetting::set('bot_name', $data['bot_name']);
        ChatSetting::set('greeting', $data['greeting']);
        ChatSetting::set('is_active', $request->boolean('is_active') ? '1' : '0');
        ChatSetting::set('ai_provider', $data['ai_provider']);
        ChatSetting::set('ai_model', $data['ai_model'] ?: 'gemini-2.0-flash');
        if (! empty($data['ai_api_key'])) ChatSetting::set('ai_api_key', trim($data['ai_api_key']));
        return back()->with('success', 'Pengaturan chatbot disimpan.');
    }

    public function create()
    {
        return view('superadmin.chatbot.form', ['faq' => new ChatFaq()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'keywords' => 'required|string|max:500',
            'jawaban' => 'required|string|max:2000',
            'urutan' => 'nullable|integer|min:0',
        ]);
        $data['urutan'] = $data['urutan'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);
        ChatFaq::create($data);
        return redirect()->route('superadmin.chatbot.index')->with('success', 'FAQ chatbot ditambahkan.');
    }

    public function edit(ChatFaq $chatbot)
    {
        return view('superadmin.chatbot.form', ['faq' => $chatbot]);
    }

    public function update(Request $request, ChatFaq $chatbot)
    {
        $data = $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'keywords' => 'required|string|max:500',
            'jawaban' => 'required|string|max:2000',
            'urutan' => 'nullable|integer|min:0',
        ]);
        $data['urutan'] = $data['urutan'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);
        $chatbot->update($data);
        return redirect()->route('superadmin.chatbot.index')->with('success', 'FAQ chatbot diupdate.');
    }

    public function destroy(ChatFaq $chatbot)
    {
        $chatbot->delete();
        return back()->with('success', 'FAQ dihapus.');
    }

    public function toggle(ChatFaq $chatbot)
    {
        $chatbot->update(['is_active' => ! $chatbot->is_active]);
        return back()->with('success', 'Status FAQ diubah.');
    }

    /** Tes koneksi Gemini dengan key tersimpan. */
    public function testAi(\App\Services\AiChatService $ai)
    {
        if (! $ai->enabled()) {
            return back()->with('error', 'Mode AI belum Gemini atau API key kosong. Isi dulu lalu Simpan.');
        }
        $reply = $ai->reply('Jawab hanya dengan kata: OK', []);
        if ($reply) {
            return back()->with('success', 'Koneksi AI BERHASIL. Balasan tes: ' . strip_tags($reply));
        }
        return back()->with('error', 'Koneksi AI GAGAL. Cek key/model, lalu lihat storage/logs/laravel.log (cari "Gemini").');
    }
}
