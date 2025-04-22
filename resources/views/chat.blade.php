@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Chat with {{ $user->name }}</h5>
                        <small class="typing-status text-muted" v-if="isUserTyping">@{{ $user->name }} is typing...</small>
                    </div>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Back to Users</a>
                </div>
                <div class="card-body">
                    <div class="chat-messages p-3" id="chat-messages" ref="chatContainer" style="height: 400px; overflow-y: auto;">
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
@endsection

@section('scripts')
<script>
    const app = {
        data() {
            return {
                messages: [],
                newMessage: '',
                isUserTyping: false,
                typingTimer: null,
                userId: {{ $user->id }},
                authId: {{ auth()->id() }}
            }
        },
        watch: {
            messages() {
                this.$nextTick(() => {
                    const container = this.$refs.chatContainer;
                    container.scrollTop = container.scrollHeight;
                });
            }
        },
        methods: {
            fetchMessages() {
                axios.get(`/messages/{{ $user->id }}`)
                    .then(response => {
                        this.messages = response.data;
                    })
                    .catch(error => {
                        console.error('Error fetching messages:', error);
                        alert('Failed to load messages');
                    });
            },
            sendMessage() {
                if (!this.newMessage.trim()) return;
                
                axios.post(`/messages/{{ $user->id }}`, {
                    message: this.newMessage
                })
                .then(response => {
                    this.messages.push(response.data);
                    this.newMessage = '';
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    alert('Failed to send message');
                });
            },
            sendTypingEvent() {
                // Use Echo to broadcast typing event
                window.Echo.private(`chat.${this.userId}`).whisper('typing', {
                    user: this.authId,
                    typing: true
                });
            },
            formatTime(datetime) {
                if (!datetime) return '';
                const date = new Date(datetime);
                return date.getHours().toString().padStart(2, '0') + ':' + 
                       date.getMinutes().toString().padStart(2, '0');
            }
        },
        mounted() {
            // Fetch initial messages
            this.fetchMessages();
            
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
            window.Echo.private(`chat.${this.authId}`)
                .listen('MessageSent', (e) => {
                    this.messages.push(e.message);
                    this.isUserTyping = false;
                    clearTimeout(this.typingTimer);
                })
                .listenForWhisper('typing', (e) => {
                    if (e.user === this.userId) {
                        this.isUserTyping = true;
                        
                        // Clear previous timer
                        clearTimeout(this.typingTimer);
                        
                        // Set timer to hide "is typing" after 2 seconds
                        this.typingTimer = setTimeout(() => {
                            this.isUserTyping = false;
                        }, 2000);
                    }
                });
        }
    };
    
    Vue.createApp(app).mount('#app');
</script>
@endsection