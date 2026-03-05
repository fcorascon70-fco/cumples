const SUPABASE_URL = "https://xbzyvpcqtmyhrtgkgizm.supabase.co";
const SUPABASE_KEY = "sb_publishable_lqwGzBuvDXFT1stqUb_iDw_ta9DZlKt";

const supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

const monthSelect = document.getElementById('month-select');
const daySelect = document.getElementById('day-select');
const searchBtn = document.getElementById('search-btn');
const resultsGrid = document.getElementById('results-grid');
const loading = document.getElementById('loading');

// Login Elements
const loginOverlay = document.getElementById('login-overlay');
const appContainer = document.getElementById('app-container');
const loginForm = document.getElementById('login-form');
const logoutBtn = document.getElementById('logout-btn');
const userDisplay = document.getElementById('user-display');
const loginError = document.getElementById('login-error');

async function init() {
    checkSession();
    try {
        await Promise.all([
            fetchMonths(),
            fetchDays()
        ]);
    } catch (error) {
        console.error('Initialization error:', error);
    }
}

function checkSession() {
    const user = localStorage.getItem('birthday_user');
    if (user) {
        showApp(user);
    }
}

function showApp(username) {
    if (loginOverlay) loginOverlay.classList.add('hidden');
    if (appContainer) appContainer.classList.remove('hidden');
    if (userDisplay) userDisplay.textContent = `Bienvenido, ${username}`;
}

async function handleLogin(e) {
    e.preventDefault();
    const user = document.getElementById('username').value;
    const pass = document.getElementById('password').value;

    if (loginError) loginError.classList.add('hidden');

    try {
        const { data, error } = await supabaseClient
            .from('usuarios')
            .select('*')
            .eq('usuario', user)
            .eq('password', pass)
            .maybeSingle();

        if (error || !data) {
            if (loginError) loginError.classList.remove('hidden');
            return;
        }

        localStorage.setItem('birthday_user', data.usuario);
        showApp(data.usuario);
    } catch (err) {
        if (loginError) loginError.classList.remove('hidden');
    }
}

function handleLogout() {
    localStorage.removeItem('birthday_user');
    location.reload();
}

async function fetchMonths() {
    const { data, error } = await supabaseClient
        .from('mes')
        .select('*')
        .order('mesid', { ascending: true });

    if (error) throw error;

    data.forEach(m => {
        const option = document.createElement('option');
        option.value = m.mesid;
        option.textContent = m.mes;
        monthSelect.appendChild(option);
    });
}

async function fetchDays() {
    const { data, error } = await supabaseClient
        .from('dias')
        .select('*')
        .order('dia', { ascending: true });

    if (error) throw error;

    data.forEach(d => {
        const option = document.createElement('option');
        option.value = d.dia;
        option.textContent = d.dia;
        daySelect.appendChild(option);
    });
}

async function searchMiembros() {
    const monthId = monthSelect.value;
    const day = daySelect.value;

    if (!monthId || !day) {
        alert('Por favor selecciona un mes y un día.');
        return;
    }

    loading.classList.remove('hidden');
    resultsGrid.innerHTML = '';

    try {
        const { data, error } = await supabaseClient
            .from('miembros')
            .select('nombre_completo, dia, celular, email')
            .eq('mes', monthId.toString())
            .eq('dia', day.toString())
            .order('nombre_completo', { ascending: true });

        if (error) throw error;

        renderResults(data);
    } catch (err) {
        console.error('Search error:', err);
        resultsGrid.innerHTML = `<div class="empty-state" style="color:var(--error)">Error al buscar: ${err.message}</div>`;
    } finally {
        loading.classList.add('hidden');
    }
}

function renderResults(members) {
    if (!members || members.length === 0) {
        resultsGrid.innerHTML = '<div class="empty-state">No se encontraron miembros para esta fecha.</div>';
        return;
    }

    const tableWrapper = document.createElement('div');
    tableWrapper.className = 'table-wrapper glass';

    let tableHtml = `
        <table class="grid-table">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Día</th>
                    <th>Celular / Teléfono</th>
                    <th>Correo</th>
                </tr>
            </thead>
            <tbody>
    `;

    members.forEach(m => {
        const initials = m.nombre_completo
            ? m.nombre_completo.split(' ').filter(p => p.length > 0).map(n => n[0]).slice(0, 2).join('').toUpperCase()
            : '?';

        tableHtml += `
            <tr>
                <td>
                    <div class="name-cell">
                        <div class="mini-avatar">${initials}</div>
                        <span class="name">${m.nombre_completo || 'Sin nombre'}</span>
                    </div>
                </td>
                <td><span class="badge-dia">${m.dia || '-'}</span></td>
                <td>${m.celular || 'N/A'}</td>
                <td>
                    ${m.email ? `<a href="mailto:${m.email}" class="email-link">${m.email}</a>` : 'N/A'}
                </td>
            </tr>
        `;
    });

    tableHtml += `</tbody></table>`;
    tableWrapper.innerHTML = tableHtml;
    resultsGrid.appendChild(tableWrapper);
}

if (loginForm) loginForm.addEventListener('submit', handleLogin);
if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);
searchBtn.addEventListener('click', searchMiembros);

init();
