const RELS = @json($relationMeta);
const INITIAL_MEMBERS = @json($initialMembersData);
const IS_AUTH = @json(auth()->check());
const TREE_BASE_W = 1800;
const TREE_BASE_H = 1100;
let treeZoom = 1;

let members = [];
let selEmoji = 'image/jaal_huu.png';
let photoData = null;
let selectedMemberId = null;

function esc(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function isImgPath(path) {
    return path && (path.startsWith('image/') || path.startsWith('/') || path.startsWith('data:') || path.startsWith('http'));
}

function loadMembers() {
    if (IS_AUTH) {
        members = JSON.parse(JSON.stringify(INITIAL_MEMBERS));
        return;
    }

    try {
        members = JSON.parse(localStorage.getItem('fm_members') || 'null') || JSON.parse(JSON.stringify(INITIAL_MEMBERS));
    } catch (e) {
        members = JSON.parse(JSON.stringify(INITIAL_MEMBERS));
    }
}

function getAvatarHtml(member) {
    if (member.photo) {
        return `<img src="${member.photo}" alt="${esc(member.name)}" class="h-full w-full object-cover"/>`;
    }
    if (isImgPath(member.emoji)) {
        return `<img src="${member.emoji}" alt="${esc(member.name)}" class="h-full w-full object-cover"/>`;
    }
    return `<span class="text-2xl">${esc(member.emoji || 'P')}</span>`;
}

function setSelectedMember(id) {
    const member = members.find((m) => m.id === id);
    if (!member) {
        return;
    }

    selectedMemberId = id;
    const rel = RELS[member.rel] || { label: member.rel };

    document.getElementById('sel-name').textContent = member.name;
    document.getElementById('sel-rel').textContent = rel.label || member.rel;
    document.getElementById('sel-name-meta').textContent = member.name || '-';
    document.getElementById('sel-rel-meta').textContent = rel.label || member.rel || '-';
    document.getElementById('sel-bio-meta').textContent = member.bio || '-';
    document.getElementById('sel-av-area').innerHTML = getAvatarHtml(member);

    const deleteForm = document.getElementById('side-delete-form');
    if (deleteForm) {
        deleteForm.action = `/family-tree/${id}`;
    }

    const msg = document.getElementById('deer-msg');
    if (msg) {
        msg.textContent = `${member.name} selected.`;
    }

    renderTree();
}

function buildLayout() {
    const me = members.find((m) => m.rel === 'me') || members[0];
    const dad = members.find((m) => m.rel === 'dad');
    const mom = members.find((m) => m.rel === 'mom');
    const siblings = members.filter((m) => m.rel === 'sib' && m.id !== (me ? me.id : -1));
    const children = members.filter((m) => m.rel === 'child');
    const others = members.filter((m) => ![me?.id, dad?.id, mom?.id].includes(m.id) && m.rel !== 'sib' && m.rel !== 'child');

    const nodes = [];
    const links = [];

    const nodeW = 220;
    const nodeH = 100;

    const meNode = me ? { ...me, x: 900, y: 620, w: nodeW, h: nodeH } : null;
    if (meNode) {
        nodes.push(meNode);
    }

    let momNode = null;
    let dadNode = null;

    if (mom) {
        momNode = { ...mom, x: 760, y: 360, w: nodeW, h: nodeH };
        nodes.push(momNode);
    }

    if (dad) {
        dadNode = { ...dad, x: 1040, y: 360, w: nodeW, h: nodeH };
        nodes.push(dadNode);
    }

    if (meNode && momNode && dadNode) {
        links.push({ x1: momNode.x + nodeW, y1: momNode.y + nodeH / 2, x2: dadNode.x, y2: dadNode.y + nodeH / 2 });
        const jx = (momNode.x + nodeW + dadNode.x) / 2;
        const jy = momNode.y + nodeH / 2;
        links.push({ x1: jx, y1: jy, x2: jx, y2: meNode.y });
        links.push({ x1: meNode.x + nodeW / 2, y1: meNode.y, x2: jx, y2: meNode.y });
    } else if (meNode && momNode) {
        links.push({ x1: momNode.x + nodeW / 2, y1: momNode.y + nodeH, x2: meNode.x + nodeW / 2, y2: meNode.y });
    } else if (meNode && dadNode) {
        links.push({ x1: dadNode.x + nodeW / 2, y1: dadNode.y + nodeH, x2: meNode.x + nodeW / 2, y2: meNode.y });
    }

    siblings.forEach((s, index) => {
        const x = 1180 + (index * 260);
        const y = 620;
        const node = { ...s, x, y, w: nodeW, h: nodeH };
        nodes.push(node);
        if (meNode) {
            links.push({ x1: meNode.x + nodeW, y1: meNode.y + nodeH / 2, x2: node.x, y2: node.y + nodeH / 2 });
        }
    });

    children.forEach((c, index) => {
        const startX = 900 - ((children.length - 1) * 130);
        const x = startX + (index * 260);
        const y = 860;
        const node = { ...c, x, y, w: nodeW, h: nodeH };
        nodes.push(node);
        if (meNode) {
            links.push({ x1: meNode.x + nodeW / 2, y1: meNode.y + nodeH, x2: node.x + nodeW / 2, y2: node.y });
        }
    });

    others.forEach((o, index) => {
        const cols = 4;
        const row = Math.floor(index / cols);
        const col = index % cols;
        const x = 220 + (col * 260);
        const y = 120 + (row * 150);
        nodes.push({ ...o, x, y, w: nodeW, h: nodeH });
    });

    return { nodes, links };
}

function renderTree() {
    const svg = document.getElementById('tree-svg');
    if (!svg) {
        return;
    }

    const { nodes, links } = buildLayout();

    let linksMarkup = '';
    links.forEach((line) => {
        linksMarkup += `<line x1="${line.x1}" y1="${line.y1}" x2="${line.x2}" y2="${line.y2}" stroke="#6b7280" stroke-width="2" />`;
    });

    let nodesMarkup = '';
    nodes.forEach((node) => {
        const rel = RELS[node.rel] || { label: node.rel, color: '#f3f4f6', stroke: '#8a8a8a' };
        const selected = selectedMemberId === node.id;
        const strokeWidth = selected ? 4 : 2;

        nodesMarkup += `
            <g class="cursor-pointer" onclick="setSelectedMember(${node.id})">
                <rect x="${node.x}" y="${node.y}" rx="16" ry="16" width="${node.w}" height="${node.h}" fill="${rel.color}" stroke="${rel.stroke}" stroke-width="${strokeWidth}" />
                <text x="${node.x + node.w / 2}" y="${node.y + 48}" text-anchor="middle" font-size="20" fill="#334155" font-family="Nunito, sans-serif">${esc(node.name)}</text>
                <text x="${node.x + node.w / 2}" y="${node.y + 74}" text-anchor="middle" font-size="12" fill="#64748b" font-family="Nunito, sans-serif">${esc(rel.label || node.rel)}</text>
            </g>`;
    });

    svg.setAttribute('viewBox', `0 0 ${TREE_BASE_W} ${TREE_BASE_H}`);
    svg.setAttribute('width', String(TREE_BASE_W * treeZoom));
    svg.setAttribute('height', String(TREE_BASE_H * treeZoom));
    svg.innerHTML = `
        <rect x="0" y="0" width="${TREE_BASE_W}" height="${TREE_BASE_H}" fill="#f8fafc"/>
        ${linksMarkup}
        ${nodesMarkup}
    `;

    updateZoomLabel();
}

function updateZoomLabel() {
    const label = document.getElementById('zoom-label');
    if (label) {
        label.textContent = `${Math.round(treeZoom * 100)}%`;
    }
}

function setZoom(next) {
    treeZoom = Math.max(0.5, Math.min(2.2, next));
    renderTree();
}

function zoomIn() {
    setZoom(treeZoom + 0.1);
}

function zoomOut() {
    setZoom(treeZoom - 0.1);
}

function zoomReset() {
    setZoom(1);
}

function printTree() {
    const svg = document.getElementById('tree-svg');
    if (!svg) {
        return;
    }

    const popup = window.open('', '_blank', 'width=1200,height=900');
    if (!popup) {
        return;
    }

    popup.document.write(`
        <html>
        <head><title>Print Tree</title></head>
        <body style="margin:0;display:flex;justify-content:center;align-items:center;background:#fff;">${svg.outerHTML}</body>
        </html>
    `);
    popup.document.close();
    popup.focus();
    popup.print();
}

function downloadTreePng() {
    const svg = document.getElementById('tree-svg');
    if (!svg) {
        return;
    }

    const serializer = new XMLSerializer();
    const source = serializer.serializeToString(svg);
    const svgBlob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(svgBlob);

    const image = new Image();
    image.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = TREE_BASE_W;
        canvas.height = TREE_BASE_H;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(image, 0, 0);

        const link = document.createElement('a');
        link.download = 'family-tree.png';
        link.href = canvas.toDataURL('image/png');
        link.click();

        URL.revokeObjectURL(url);
    };
    image.src = url;
}

function openAddModal() {
    const modal = document.getElementById('add-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAddModal(event) {
    const modal = document.getElementById('add-modal');
    if (event && event.target !== modal) {
        return;
    }
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openEditModal() {
    const member = members.find((m) => m.id === selectedMemberId);
    if (!member) {
        openAddModal();
        return;
    }

    document.querySelector('[name="name"]').value = member.name || '';
    document.querySelector('[name="rel"]').value = member.rel || '';
    document.querySelector('[name="bio"]').value = member.bio || '';
    openAddModal();
}

function openAddByRelation(rel) {
    document.querySelector('[name="rel"]').value = rel;
    openAddModal();
    document.querySelector('[name="name"]').focus();
}

function deleteLocal() {
    if (!selectedMemberId) {
        return;
    }

    if (!confirm('Delete this person?')) {
        return;
    }

    members = members.filter((m) => m.id !== selectedMemberId);

    try {
        localStorage.setItem('fm_members', JSON.stringify(members));
    } catch (e) {}

    if (members.length) {
        const fallback = members.find((m) => m.rel === 'me') || members[0];
        setSelectedMember(fallback.id);
    } else {
        selectedMemberId = null;
        document.getElementById('sel-name').textContent = 'Not Selected';
        document.getElementById('sel-rel').textContent = '-';
        document.getElementById('sel-name-meta').textContent = '-';
        document.getElementById('sel-rel-meta').textContent = '-';
        document.getElementById('sel-bio-meta').textContent = '-';
        document.getElementById('sel-av-area').innerHTML = '';
        renderTree();
    }
}

function pickEmoji(button) {
    document.querySelectorAll('#emoji-row button').forEach((item) => {
        item.classList.remove('border-green-500', 'bg-green-100');
        item.classList.add('border-transparent', 'bg-gray-100');
    });

    button.classList.remove('border-transparent', 'bg-gray-100');
    button.classList.add('border-green-500', 'bg-green-100');

    selEmoji = button.dataset.e;
    document.getElementById('fi-emoji').value = selEmoji;
}

function previewImg(input) {
    const file = input.files[0];
    if (!file) {
        return;
    }

    if (!IS_AUTH) {
        const reader = new FileReader();
        reader.onload = (event) => {
            photoData = event.target.result;
            document.getElementById('upload-inner').innerHTML = `<img class="mx-auto h-20 w-20 rounded-full border-4 border-green-500 object-cover" src="${photoData}"/><div class="mt-1 text-center text-xs font-black text-green-600">Ready</div>`;
        };
        reader.readAsDataURL(file);
        return;
    }

    const tempUrl = URL.createObjectURL(file);
    document.getElementById('upload-inner').innerHTML = `<img class="mx-auto h-20 w-20 rounded-full border-4 border-green-500 object-cover" src="${tempUrl}"/><div class="mt-1 text-center text-xs font-black text-green-600">Ready</div>`;
}

function handleAddSubmit(event) {
    if (IS_AUTH) {
        return true;
    }

    event.preventDefault();

    const nameInput = document.querySelector('[name="name"]');
    const relInput = document.querySelector('[name="rel"]');
    const bioInput = document.querySelector('[name="bio"]');
    const submitButton = document.querySelector('#add-form button[type="submit"]');

    const name = nameInput.value.trim();
    const rel = relInput.value;
    const bio = bioInput.value.trim();

    if (!name || !rel) {
        submitButton.textContent = 'Please enter name and relation';
        submitButton.classList.remove('bg-green-600', 'hover:bg-green-700');
        submitButton.classList.add('bg-red-600');

        setTimeout(() => {
            submitButton.textContent = 'Save';
            submitButton.classList.remove('bg-red-600');
            submitButton.classList.add('bg-green-600', 'hover:bg-green-700');
        }, 1800);

        return false;
    }

    const nextId = Math.max(...members.map((m) => m.id), 0) + 1;
    members.push({ id: nextId, name, rel, emoji: selEmoji, bio: bio || 'Family member', photo: photoData });

    try {
        localStorage.setItem('fm_members', JSON.stringify(members));
    } catch (e) {}

    nameInput.value = '';
    relInput.value = '';
    bioInput.value = '';
    photoData = null;
    selEmoji = 'image/jaal_huu.png';
    document.getElementById('fi-emoji').value = selEmoji;

    document.querySelectorAll('#emoji-row button').forEach((item, index) => {
        item.classList.remove('border-green-500', 'bg-green-100');
        item.classList.add(index === 0 ? 'border-green-500' : 'border-transparent');
        item.classList.add(index === 0 ? 'bg-green-100' : 'bg-gray-100');
    });

    document.getElementById('upload-inner').innerHTML = '<div class="text-lg font-bold text-slate-600">Select image</div><div class="mt-1 text-xs text-gray-400">Click to upload</div>';

    submitButton.textContent = 'Saved';
    submitButton.classList.remove('bg-green-600', 'hover:bg-green-700');
    submitButton.classList.add('bg-orange-500');

    setTimeout(() => {
        submitButton.textContent = 'Save';
        submitButton.classList.remove('bg-orange-500');
        submitButton.classList.add('bg-green-600', 'hover:bg-green-700');
    }, 1400);

    setSelectedMember(nextId);
    closeAddModal();
    return false;
}

loadMembers();

if (members.length) {
    const startMember = members.find((m) => m.rel === 'me') || members[0];
    setSelectedMember(startMember.id);
} else {
    renderTree();
}

@if($errors->any())
openAddModal();
@endif
