@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Chat with {{ $user->name }}</h5>
                        <small class="typing-status text-muted" id="typing-status" style="display: none;">{{ $user->name }} is typing...</small>
                    </div>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Back to Users</a>
                </div>
                <div class="card-body">
                    <div id="chat-app">
                        <div class="chat-messages p-3" ref="chatContainer" style="height: 400px; overflow-y: auto;">
                            <div v-for="message in messages" :key="message.id" class="mb-3" :class="{'text-end': message.sender_id === {{ auth()->id() }}}">
                                <div :class="{'float-end': message.sender_id === {{ auth()->id() }}, 'float-start': message.sender_id !== {{ auth()->id() }}}">
                                    <div class="message-content p-3 rounded" 
                                        :class="{'bg-primary text-white': message.sender_id === {{ auth()->id() }}, 'bg-light': message.sender_id !== {{ auth()->id() }}}">
                                        @{{ message.text }}
                                    </div>
                                    <small class="text-muted d-block">@{{ formatTime(message.created_at) }}</small>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <hr>
                        <form @submit.prevent="sendMessage" class="mt-3">
                            <div class="input-group">
                                <input type="text" v-model="newMessage" @input="sendTypingEvent" class="form-control" placeholder="Type your message..." required>
                                <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const { createApp, ref, watch, nextTick, onMounted } = Vue;
        
        const chatApp = createApp({
            setup() {
                const messages = ref([]);
                const newMessage = ref('');
                const isUserTyping = ref(false);
                let typingTimer = null;
                const chatContainer = ref(null);
                
                const userId = {{ $user->id }};
                const authId = {{ auth()->id() }};
                
                watch(messages, () => {
                    nextTick(() => {
                        if (chatContainer.value) {
                            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
                        }
                    });
                });
                
                function fetchMessages() {
                    axios.get(`/messages/{{ $user->id }}`)
                        .then(response => {
                            messages.value = response.data;
                        })
                        .catch(error => {
                            console.error('Error fetching messages:', error);
                            alert('Failed to load messages');
                        });
                }
                
                function sendMessage() {
                    if (!newMessage.value.trim()) return;
                    
                    axios.post(`/messages/{{ $user->id }}`, {
                        message: newMessage.value
                    })
                    .then(response => {
                        messages.value.push(response.data);
                        newMessage.value = '';
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        alert('Failed to send message');
                    });
                }
                
                function sendTypingEvent() {
                    try {
                        // Use Echo to broadcast typing event
                        window.Echo.private(`chat.${userId}`).whisper('typing', {
                            user: authId,
                            typing: true
                        });
                    } catch(error) {
                        console.error('Error sending typing event:', error);
                    }
                }
                
                function formatTime(datetime) {
                    if (!datetime) return '';
                    const date = new Date(datetime);
                    return date.getHours().toString().padStart(2, '0') + ':' + 
                           date.getMinutes().toString().padStart(2, '0');
                }
                
                onMounted(() => {
                    // Fetch initial messages
                    fetchMessages();
                    
                    try {
                        // Monitor presence channel to see who's online
                        window.Echo.join('presence.chat')
                            .here((users) => {
                                console.log('Online users:', users);
                            })
                            .joining((user) => {
                                console.log('User joined:', user);
                            })
                            .leaving((user) => {
                                console.log('User left:', user);
                            });
                            
                        // Listen for new messages on the private channel
                        window.Echo.private(`chat.${authId}`)
                            .listen('MessageSent', (e) => {
                                messages.value.push(e.message);
                                document.getElementById('typing-status').style.display = 'none';
                                clearTimeout(typingTimer);
                            })
                            .listenForWhisper('typing', (e) => {
                                if (e.user === userId) {
                                    document.getElementById('typing-status').style.display = 'block';
                                    
                                    // Clear previous timer
                                    clearTimeout(typingTimer);
                                    
                                    // Set timer to hide "is typing" after 2 seconds
                                    typingTimer = setTimeout(() => {
                                        document.getElementById('typing-status').style.display = 'none';
                                    }, 2000);
                                }
                            });
                    } catch(error) {
                        console.error('Error setting up Echo listeners:', error);
                    }
                });
                
                return {
                    messages,
                    newMessage,
                    chatContainer,
                    sendMessage,
                    sendTypingEvent,
                    formatTime
                };
            }
        }).mount('#chat-app');
    });
</script>
@endsection