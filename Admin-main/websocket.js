import CONFIG from './config.js';

let ws; // WebSocket instance

export function connectWebSocket() {
    const token = localStorage.getItem('userToken');
    const email = localStorage.getItem('userEmail');

    if (!token || !email) {
        console.error('No token or email found. Cannot connect to WebSocket.');
        return;
    }

    const webSocketWithTokenUrl = `${CONFIG.WS_BASE_URL}?token=${encodeURIComponent(token)}`;
    console.log(`[WebSocket] Connecting to: ${webSocketWithTokenUrl}`);

    ws = new WebSocket(webSocketWithTokenUrl);

    ws.onopen = () => {
        console.log('[WebSocket] Connection established.');
    };

    ws.onclose = () => {
        console.warn('[WebSocket] Connection closed.');
    };

    ws.onerror = (error) => {
        console.error('[WebSocket] Error encountered:', error);
    };
}