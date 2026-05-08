function generateWhatsAppMessage(type) {
    const box = document.getElementById('wa-msg-box');
    const msgData = window.waMessageData;
    if (!msgData || !box) return;

    let text = "";
    if (type === 'motivational') text = msgData.motivational;
    else if (type === 'funny') text = msgData.funny;
    else text = msgData.simple;

    box.textContent = text;
}

function copyMessage() {
    const box = document.getElementById('wa-msg-box');
    if (!box || !box.textContent) return;
    
    navigator.clipboard.writeText(box.textContent).then(() => {
        const btn = document.getElementById('copy-btn');
        const oldText = btn.textContent;
        btn.textContent = window.waCopiedText || 'Copied!';
        setTimeout(() => { btn.textContent = oldText; }, 2000);
    });
}
