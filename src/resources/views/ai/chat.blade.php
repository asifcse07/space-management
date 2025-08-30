@extends('layouts.app')

@section('title', 'AI Space Assistant')
@section('header', 'Space Management AI Assistant')

@section('content')
<div class="messages" id="messages"></div>

<form id="chat-form" style="display:flex;margin-top:10px;">
    <input type="text" id="query" placeholder="Ask something about your buildings..." required style="flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;">
    <button type="submit" style="padding:10px 15px;margin-left:8px;border:none;background:#28a745;color:white;border-radius:6px;cursor:pointer;">Send</button>
</form>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const form = document.getElementById('chat-form');
    const queryInput = document.getElementById('query');
    const messagesDiv = document.getElementById('messages');

    function appendMessage(text, sender) {
        const msg = document.createElement('div');
        msg.classList.add('msg', sender);
        msg.style.margin='10px 0';
        msg.style.padding='10px 15px';
        msg.style.borderRadius='15px';
        msg.style.maxWidth='70%';
        msg.style.clear='both';
        msg.style.background = sender === 'user' ? '#007bff' : '#f1f1f1';
        msg.style.color = sender === 'user' ? 'white' : 'black';
        msg.style.float = sender === 'user' ? 'right' : 'left';
        msg.innerText = text;
        messagesDiv.appendChild(msg);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let query = queryInput.value.trim();
        if (!query) return;

        appendMessage(query, 'user');
        queryInput.value = '';

        axios.post('/ai/chat', { query: query })
            .then(res => appendMessage(res.data.answer, 'ai'))
            .catch(err => appendMessage('Error: ' + err, 'ai'));
    });
</script>
@endsection
