import './bootstrap';

// Presence channel subscription for chat rooms
const roomElement = document.getElementById('chat-room');
if (roomElement) {
    const roomId = roomElement.dataset.roomId;
    window.Echo.join(`presence-chat-room.${roomId}`)
        .here((users) => {
            console.log('Online users:', users);
        })
        .joining((user) => {
            console.log('User joined:', user);
        })
        .leaving((user) => {
            console.log('User left:', user);
        });
}
