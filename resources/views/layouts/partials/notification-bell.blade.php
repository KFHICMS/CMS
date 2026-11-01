{{-- Top-bar notification bell: unread messages from other users + login reminder + optional desktop alerts. --}}
@if (\Illuminate\Support\Facades\Route::has('messages.notifications'))
<div class="k-bell" id="k-bell"
     data-url="{{ route('messages.notifications') }}"
     data-user="{{ auth()->id() }}">
    <button type="button" class="k-icon-btn" id="k-bell-btn" aria-haspopup="true" aria-expanded="false"
            aria-controls="k-bell-panel" aria-label="Notifications">
        <svg class="k-icon" aria-hidden="true"><use href="#i-bell"></use></svg>
        <span class="k-bell-count" id="k-bell-count" hidden>0</span>
    </button>

    <div class="k-notif-panel" id="k-bell-panel" hidden>
        <div class="k-notif-head">
            <strong>Notifications</strong>
            <span id="k-bell-sub"></span>
        </div>
        <ul class="k-notif-list" id="k-bell-list"></ul>
        <p class="k-notif-empty" id="k-bell-empty">No new messages.</p>
        <div class="k-notif-foot">
            <a href="{{ route('messages.index') }}">Open inbox</a>
            <button type="button" id="k-bell-enable" hidden>Turn on desktop alerts</button>
        </div>
    </div>

    <div class="k-toast-region" id="k-toast-region" role="status" aria-live="polite"></div>
</div>

<script>
(function () {
    var root = document.getElementById('k-bell');
    if (!root) { return; }

    var url = root.dataset.url, uid = root.dataset.user;
    var btn = document.getElementById('k-bell-btn'), panel = document.getElementById('k-bell-panel');
    var list = document.getElementById('k-bell-list'), empty = document.getElementById('k-bell-empty');
    var count = document.getElementById('k-bell-count'), sub = document.getElementById('k-bell-sub');
    var enable = document.getElementById('k-bell-enable'), region = document.getElementById('k-toast-region');
    var LOGIN_KEY = 'kfhi.notif.login.' + uid, LAST_KEY = 'kfhi.notif.last.' + uid;

    function get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
    function set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* storage unavailable */ } }

    function toggle(open) { panel.hidden = !open; btn.setAttribute('aria-expanded', String(open)); }
    btn.addEventListener('click', function () { toggle(panel.hidden); });
    document.addEventListener('click', function (e) { if (!root.contains(e.target)) { toggle(false); } });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) { toggle(false); btn.focus(); }
    });

    // Desktop (browser) notifications: only after the person clicks the button, and only on HTTPS/localhost.
    function canDesktop() { return 'Notification' in window && window.isSecureContext; }
    function refreshEnable() { enable.hidden = !(canDesktop() && Notification.permission === 'default'); }
    enable.addEventListener('click', function () {
        Notification.requestPermission().then(refreshEnable, refreshEnable);
    });
    refreshEnable();

    function toast(text, href) {
        var t = document.createElement('a');
        t.className = 'k-toast';
        t.href = href;
        t.textContent = text;
        region.appendChild(t);
        setTimeout(function () { if (t.parentNode) { t.parentNode.removeChild(t); } }, 9000);
    }

    function desktop(text, href) {
        if (!canDesktop() || Notification.permission !== 'granted') { return; }
        var n = new Notification('KFHI Child Management System', { body: text, tag: 'kfhi-messages' });
        n.onclick = function () { window.focus(); window.location.href = href; n.close(); };
    }

    function announce(text, href) { toast(text, href); desktop(text, href); }

    function render(d) {
        var n = d.unread;
        count.hidden = n < 1;
        count.textContent = n > 99 ? '99+' : String(n);
        btn.setAttribute('aria-label', n > 0 ? 'Notifications, ' + n + ' unread' : 'Notifications');
        sub.textContent = n > 0 ? n + ' unread' : '';
        list.textContent = '';
        d.items.forEach(function (it) {
            var li = document.createElement('li'), a = document.createElement('a');
            var s = document.createElement('strong'), f = document.createElement('span');
            a.href = it.url;
            s.textContent = it.subject;
            f.textContent = 'From ' + it.from + ' · ' + it.time;
            a.appendChild(s); a.appendChild(f); li.appendChild(a); list.appendChild(li);
        });
        empty.hidden = d.items.length > 0;
    }

    function handle(d) {
        render(d);

        var ids = d.items.map(function (i) { return i.message_id; }).filter(Boolean);
        var maxId = ids.length ? Math.max.apply(null, ids) : 0;
        var last = parseInt(get(LAST_KEY) || '0', 10);
        var loginAt = d.login_at ? String(d.login_at) : '';

        if (loginAt && get(LOGIN_KEY) !== loginAt) {
            // First page after a new login: remind about everything unread.
            set(LOGIN_KEY, loginAt);
            if (d.unread > 0) {
                announce('You have ' + d.unread + ' unread message' + (d.unread === 1 ? '' : 's') + '.', d.inbox_url);
            }
        } else {
            var fresh = d.items.filter(function (i) { return i.message_id > last; });
            if (fresh.length === 1) {
                announce('New message from ' + fresh[0].from + ': ' + fresh[0].subject, fresh[0].url);
            } else if (fresh.length > 1) {
                announce('You have ' + fresh.length + ' new messages.', d.inbox_url);
            }
        }
        if (maxId > last) { set(LAST_KEY, String(maxId)); }
    }

    function check() {
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) { if (d) { handle(d); } })
            .catch(function () { /* offline: try again next time */ });
    }

    check();
    setInterval(function () { if (!document.hidden) { check(); } }, 60000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { check(); } });
})();
</script>
@endif