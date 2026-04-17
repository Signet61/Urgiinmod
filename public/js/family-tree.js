(function () {
    const boot = window.FAMILY_TREE_BOOT || {};
    const RELS = boot.rels || {};
    const INITIAL_MEMBERS = boot.initialMembers || [];
    const IS_AUTH = !!boot.isAuth;
    const HAS_ERRORS = !!boot.hasErrors;

    let TREE_BASE_W = 1600;
    let TREE_BASE_H = 1120;

    let treeZoom = 1;
    let members = [];
    let selEmoji = 'image/jaal_huu.png';
    let photoData = null;
    let selectedMemberId = null;
    let editingMemberId = null;
    let hasAutoCentered = false;

    function esc(text) {
        return String(text ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function truncateText(text, maxChars) {
        const value = String(text ?? '');
        if (value.length <= maxChars) {
            return value;
        }

        return value.slice(0, Math.max(1, maxChars - 1)) + '…';
    }

    function isImgPath(path) {
        return path && (path.startsWith('image/') || path.startsWith('/') || path.startsWith('data:') || path.startsWith('http'));
    }

    function toId(value) {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : null;
    }

    function normalizeMember(member) {
        return {
            ...member,
            id: toId(member.id),
            related_to_id: toId(member.related_to_id),
        };
    }

    function getMemberById(id) {
        const normalizedId = toId(id);
        if (!normalizedId) {
            return null;
        }

        return members.find((item) => item.id === normalizedId) || null;
    }

    function getSelectedMember() {
        return getMemberById(selectedMemberId);
    }

    function getMeMember() {
        return members.find((item) => item.rel === 'me') || members[0] || null;
    }

    function getRelatedToInput() {
        return document.getElementById('fi-related-to-id');
    }

    function findPartnerFor(memberId) {
        const ownerId = toId(memberId);
        if (!ownerId) {
            return null;
        }

        return members.find((item) => item.rel === 'partner' && toId(item.related_to_id) === ownerId) || null;
    }

    function getChildrenOf(memberId) {
        const ownerId = toId(memberId);
        if (!ownerId) {
            return [];
        }

        return members.filter((item) => item.rel === 'child' && toId(item.related_to_id) === ownerId);
    }

    function getBaseDescendantOwner(member) {
        if (!member) {
            return null;
        }

        if ((member.rel === 'partner' || member.rel === 'child') && member.related_to_id) {
            return getMemberById(member.related_to_id) || member;
        }

        return member;
    }

    function estimateNodeLabelWidth(member) {
        const nameLength = (member?.name || '').length;
        return Math.max(112, 72 + (nameLength * 8));
    }

    function estimateUnitWidth(unit) {
        const primaryWidth = estimateNodeLabelWidth(unit.primary);
        const partnerWidth = unit.partner ? estimateNodeLabelWidth(unit.partner) : 0;
        if (unit.partner) {
            return primaryWidth + partnerWidth + 92;
        }

        return primaryWidth;
    }

    function getAssetUrl(path) {
        if (!path) {
            return '';
        }
        if (path.startsWith('http') || path.startsWith('data:') || path.startsWith('/')) {
            return path;
        }
        return '/' + path;
    }

    function loadMembers() {
        if (IS_AUTH) {
            members = JSON.parse(JSON.stringify(INITIAL_MEMBERS)).map(normalizeMember);
            return;
        }

        try {
            const local = JSON.parse(localStorage.getItem('fm_members') || 'null');
            members = (local || JSON.parse(JSON.stringify(INITIAL_MEMBERS))).map(normalizeMember);
        } catch (error) {
            members = JSON.parse(JSON.stringify(INITIAL_MEMBERS)).map(normalizeMember);
        }
    }

    function getAvatarHtml(member) {
        if (member.photo) {
            return '<img src="' + member.photo + '" alt="' + esc(member.name) + '" class="h-full w-full object-cover"/>';
        }

        if (isImgPath(member.emoji)) {
            return '<img src="' + getAssetUrl(member.emoji) + '" alt="' + esc(member.name) + '" class="h-full w-full object-cover"/>';
        }

        return '<span class="text-2xl">' + esc(member.emoji || 'Х') + '</span>';
    }

    function setSelectedMember(id) {
        const member = getMemberById(id);
        if (!member) {
            return;
        }

        selectedMemberId = member.id;
        const rel = RELS[member.rel] || { label: member.rel };

        const nameEl = document.getElementById('sel-name');
        if (!nameEl) {
            return;
        }

        nameEl.textContent = member.name;
        document.getElementById('sel-rel').textContent = rel.label || member.rel;
        document.getElementById('sel-name-meta').textContent = member.name || '-';
        document.getElementById('sel-rel-meta').textContent = rel.label || member.rel || '-';
        document.getElementById('sel-bio-meta').textContent = member.bio || '-';
        document.getElementById('sel-av-area').innerHTML = getAvatarHtml(member);

        const deleteForm = document.getElementById('side-delete-form');
        if (deleteForm) {
            deleteForm.action = '/family-tree/' + id;
        }

        const msg = document.getElementById('deer-msg');
        if (msg) {
            msg.textContent = member.name + ' сонгогдлоо.';
        }

        renderTree();
    }

    function buildAncestorSection(nodes, links, centerX, radius) {
        const rows = {
            great: 70,
            grand: 290,
            parent: 520,
        };

        const dad = members.find((item) => item.rel === 'dad') || null;
        const mom = members.find((item) => item.rel === 'mom') || null;

        const gpaPaternal = members.find((item) => item.rel === 'gpl') || null;
        const gmaPaternal = members.find((item) => item.rel === 'gml') || null;
        const gpaMaternal = members.find((item) => item.rel === 'gpr') || null;
        const gmaMaternal = members.find((item) => item.rel === 'gmr') || null;

        const greatPairs = [
            {
                father: members.find((item) => item.rel === 'ggplf') || null,
                mother: members.find((item) => item.rel === 'ggplm') || null,
                anchor: null,
            },
            {
                father: members.find((item) => item.rel === 'ggmlf') || null,
                mother: members.find((item) => item.rel === 'ggmlm') || null,
                anchor: null,
            },
            {
                father: members.find((item) => item.rel === 'ggprf') || null,
                mother: members.find((item) => item.rel === 'ggprm') || null,
                anchor: null,
            },
            {
                father: members.find((item) => item.rel === 'ggmrf') || null,
                mother: members.find((item) => item.rel === 'ggmrm') || null,
                anchor: null,
            },
        ];

        const parentWidth = Math.max(
            estimateNodeLabelWidth(dad || {}),
            estimateNodeLabelWidth(mom || {})
        );
        const grandWidth = Math.max(
            estimateNodeLabelWidth(gpaPaternal || {}),
            estimateNodeLabelWidth(gmaPaternal || {}),
            estimateNodeLabelWidth(gpaMaternal || {}),
            estimateNodeLabelWidth(gmaMaternal || {})
        );
        const greatWidth = Math.max(
            ...greatPairs.flatMap((pair) => [estimateNodeLabelWidth(pair.father || {}), estimateNodeLabelWidth(pair.mother || {})])
        );

        const greatHalfGap = Math.max(86, Math.round((greatWidth / 2) + 18));
        const grandHalfGap = Math.max(
            160,
            Math.round((grandWidth / 2) + 30),
            greatHalfGap + 110
        );
        const parentHalfGap = Math.max(
            340,
            Math.round((parentWidth / 2) + 36),
            grandHalfGap + greatHalfGap + 140
        );

        const dadX = centerX - parentHalfGap;
        const momX = centerX + parentHalfGap;

        const parentPair = {
            left: dad ? { ...dad, x: dadX, y: rows.parent } : null,
            right: mom ? { ...mom, x: momX, y: rows.parent } : null,
        };

        if (parentPair.left) {
            nodes.push(parentPair.left);
        }
        if (parentPair.right) {
            nodes.push(parentPair.right);
        }

        const paternalPair = {
            left: gpaPaternal ? { ...gpaPaternal, x: dadX - grandHalfGap, y: rows.grand } : null,
            right: gmaPaternal ? { ...gmaPaternal, x: dadX + grandHalfGap, y: rows.grand } : null,
        };
        const maternalPair = {
            left: gpaMaternal ? { ...gpaMaternal, x: momX - grandHalfGap, y: rows.grand } : null,
            right: gmaMaternal ? { ...gmaMaternal, x: momX + grandHalfGap, y: rows.grand } : null,
        };

        [paternalPair.left, paternalPair.right, maternalPair.left, maternalPair.right]
            .filter(Boolean)
            .forEach((node) => nodes.push(node));

        greatPairs[0].anchor = paternalPair.left;
        greatPairs[1].anchor = paternalPair.right;
        greatPairs[2].anchor = maternalPair.left;
        greatPairs[3].anchor = maternalPair.right;

        greatPairs.forEach((pair) => {
            if (!pair.anchor) {
                return;
            }

            const fatherNode = pair.father ? { ...pair.father, x: pair.anchor.x - greatHalfGap, y: rows.great } : null;
            const motherNode = pair.mother ? { ...pair.mother, x: pair.anchor.x + greatHalfGap, y: rows.great } : null;

            if (fatherNode) {
                nodes.push(fatherNode);
            }
            if (motherNode) {
                nodes.push(motherNode);
            }

            if (fatherNode && motherNode) {
                links.push({ x1: fatherNode.x, y1: rows.great, x2: motherNode.x, y2: rows.great });
                const anchorX = (fatherNode.x + motherNode.x) / 2;
                links.push({ x1: anchorX, y1: rows.great + radius, x2: anchorX, y2: rows.grand - radius });
            } else if (fatherNode || motherNode) {
                const single = fatherNode || motherNode;
                links.push({ x1: single.x, y1: rows.great + radius, x2: single.x, y2: rows.grand - radius });
            }
        });

        if (parentPair.left && parentPair.right) {
            links.push({ x1: parentPair.left.x, y1: rows.parent, x2: parentPair.right.x, y2: rows.parent });
        }

        function connectGrandSide(pair, parentNode) {
            if (!parentNode) {
                return;
            }

            if (pair.left && pair.right) {
                links.push({ x1: pair.left.x, y1: rows.grand, x2: pair.right.x, y2: rows.grand });
                const anchorX = (pair.left.x + pair.right.x) / 2;
                links.push({ x1: anchorX, y1: rows.grand + radius, x2: anchorX, y2: rows.parent - radius });
            } else if (pair.left || pair.right) {
                const node = pair.left || pair.right;
                links.push({ x1: node.x, y1: rows.grand + radius, x2: node.x, y2: rows.parent - radius });
            }
        }

        connectGrandSide(paternalPair, parentPair.left);
        connectGrandSide(maternalPair, parentPair.right);

        const parentAnchorX = parentPair.left && parentPair.right
            ? (parentPair.left.x + parentPair.right.x) / 2
            : (parentPair.left ? parentPair.left.x : (parentPair.right ? parentPair.right.x : centerX));

        return {
            parentAnchorX,
            parentBottomY: rows.parent + radius,
            descendantStartY: 820,
        };
    }

    function buildDescendantLevels(rootMember) {
        const levels = [];
        if (!rootMember) {
            return levels;
        }

        const siblings = members.filter((item) => item.rel === 'sib' && item.id !== rootMember.id);

        const level0 = [{
            primary: rootMember,
            partner: findPartnerFor(rootMember.id),
            children: getChildrenOf(rootMember.id),
        }];

        siblings.forEach((sib) => {
            level0.push({
                primary: sib,
                partner: findPartnerFor(sib.id),
                children: getChildrenOf(sib.id),
            });
        });

        levels.push(level0);

        let currentChildrenIds = [...new Set(level0.flatMap((unit) => unit.children.map((child) => child.id)))];
        const visited = new Set(currentChildrenIds);
        let guard = 0;

        while (currentChildrenIds.length && guard < 12) {
            const nextLevel = [];

            currentChildrenIds.forEach((id) => {
                const primary = members.find((item) => item.id === id);
                if (!primary) {
                    return;
                }

                nextLevel.push({
                    primary,
                    partner: findPartnerFor(primary.id),
                    children: getChildrenOf(primary.id),
                });
            });

            if (!nextLevel.length) {
                break;
            }

            levels.push(nextLevel);

            const nextChildrenIds = [];
            nextLevel.forEach((unit) => {
                unit.children.forEach((child) => {
                    if (!visited.has(child.id)) {
                        visited.add(child.id);
                        nextChildrenIds.push(child.id);
                    }
                });
            });

            currentChildrenIds = nextChildrenIds;
            guard += 1;
        }

        return levels;
    }

    function buildLayout() {
        const centerX = 800;
        const radius = 55;
        const nodes = [];
        const links = [];

        const ancestorMeta = buildAncestorSection(nodes, links, centerX, radius);
        const rootMember = getMeMember();
        const levels = buildDescendantLevels(rootMember);

        const yStart = ancestorMeta.descendantStartY;
        const levelGap = 240;

        const levelMaps = [];

        levels.forEach((units, levelIndex) => {
            const y = yStart + (levelIndex * levelGap);
            const levelMap = new Map();
            const widestUnit = units.reduce((maxWidth, unit) => Math.max(maxWidth, estimateUnitWidth(unit)), 180);
            const unitGap = Math.max(300, widestUnit + 56);
            const basePairGap = Math.max(190, Math.round(widestUnit * 0.58));
            const startX = centerX - ((Math.max(units.length, 1) - 1) * unitGap) / 2;
            const anchors = [];

            if (levelIndex === 0 && units.length > 0) {
                // Keep the main unit (me + partner) fixed in center. Spread siblings around it.
                anchors[0] = centerX;
                for (let idx = 1; idx < units.length; idx += 1) {
                    const step = Math.ceil(idx / 2) * unitGap;
                    const sign = idx % 2 === 1 ? 1 : -1;
                    anchors[idx] = centerX + (sign * step);
                }
            }

            units.forEach((unit, index) => {
                const anchorX = anchors[index] ?? (startX + (index * unitGap));
                let primaryX = anchorX;
                let partnerX = null;
                const pairGap = Math.max(basePairGap, Math.round((estimateNodeLabelWidth(unit.primary) + estimateNodeLabelWidth(unit.partner || {})) * 0.44));

                if (unit.partner) {
                    primaryX = anchorX - (pairGap / 2);
                    partnerX = anchorX + (pairGap / 2);
                }

                const primaryNode = { ...unit.primary, x: primaryX, y };
                nodes.push(primaryNode);

                let partnerNode = null;
                if (unit.partner) {
                    partnerNode = { ...unit.partner, x: partnerX, y };
                    nodes.push(partnerNode);
                    links.push({ x1: primaryX, y1: y, x2: partnerX, y2: y });
                }

                levelMap.set(unit.primary.id, {
                    unit,
                    primaryNode,
                    partnerNode,
                    anchorX: partnerNode ? (primaryX + partnerX) / 2 : primaryX,
                    y,
                });
            });

            levelMaps.push(levelMap);
        });

        if (levelMaps[0] && levelMaps[0].size) {
            const rootItems = [...levelMaps[0].values()].sort((a, b) => a.anchorX - b.anchorX);
            const joinY = rootItems[0].y - 110;

            links.push({ x1: ancestorMeta.parentAnchorX, y1: ancestorMeta.parentBottomY, x2: ancestorMeta.parentAnchorX, y2: joinY });

            if (rootItems.length > 1) {
                links.push({ x1: rootItems[0].anchorX, y1: joinY, x2: rootItems[rootItems.length - 1].anchorX, y2: joinY });
            }

            rootItems.forEach((item) => {
                links.push({ x1: item.anchorX, y1: joinY, x2: item.anchorX, y2: item.y - radius });
            });
        }

        for (let levelIndex = 0; levelIndex < levelMaps.length - 1; levelIndex += 1) {
            const currentMap = levelMaps[levelIndex];
            const nextMap = levelMaps[levelIndex + 1];

            currentMap.forEach((item) => {
                const childEntries = item.unit.children
                    .map((child) => nextMap.get(child.id))
                    .filter(Boolean)
                    .sort((a, b) => a.anchorX - b.anchorX);

                if (!childEntries.length) {
                    return;
                }

                const childJoinY = childEntries[0].y - 110;
                links.push({ x1: item.anchorX, y1: item.y + radius, x2: item.anchorX, y2: childJoinY });

                if (childEntries.length > 1) {
                    links.push({ x1: childEntries[0].anchorX, y1: childJoinY, x2: childEntries[childEntries.length - 1].anchorX, y2: childJoinY });
                }

                childEntries.forEach((childItem) => {
                    links.push({ x1: childItem.anchorX, y1: childJoinY, x2: childItem.anchorX, y2: childItem.y - radius });
                });
            });
        }

        const usedIds = new Set(nodes.map((node) => node.id));
        const others = members.filter((item) => !usedIds.has(item.id));

        if (others.length) {
            const startY = yStart;
            others.forEach((item, index) => {
                const cols = 4;
                const row = Math.floor(index / cols);
                const col = index % cols;
                nodes.push({
                    ...item,
                    x: 230 + (col * 230),
                    y: startY + (row * 190),
                });
            });
        }

        return { nodes, links };
    }

    function renderTree() {
        const svg = document.getElementById('tree-svg');
        if (!svg) {
            return;
        }

        const built = buildLayout();
        const nodes = built.nodes;
        const links = built.links;

        const xs = nodes.map((n) => n.x);
        const ys = nodes.map((n) => n.y);
        const minX = xs.length ? Math.min(...xs) - 180 : 0;
        const maxX = xs.length ? Math.max(...xs) + 180 : TREE_BASE_W;
        const minY = ys.length ? Math.min(...ys) - 180 : 0;
        const maxY = ys.length ? Math.max(...ys) + 180 : TREE_BASE_H;

        const boundsWidth = Math.max(1, maxX - minX);
        const boundsHeight = Math.max(1, maxY - minY);
        const padding = 140;
        const offsetX = padding - minX;
        const offsetY = padding - minY;

        TREE_BASE_W = Math.max(1200, Math.round(boundsWidth + (padding * 2)));
        TREE_BASE_H = Math.max(980, Math.round(boundsHeight + (padding * 2)));

        let linksMarkup = '';
        links.forEach((line) => {
            linksMarkup += '<line x1="' + (line.x1 + offsetX) + '" y1="' + (line.y1 + offsetY) + '" x2="' + (line.x2 + offsetX) + '" y2="' + (line.y2 + offsetY) + '" stroke="#2b3a3c" stroke-width="3" stroke-linecap="round" />';
        });

        let nodesMarkup = '';
        nodes.forEach((node) => {
            const rel = RELS[node.rel] || { label: node.rel, color: '#f3f4f6', stroke: '#8a8a8a' };
            const selected = selectedMemberId === node.id;
            const ringWidth = selected ? 8 : 5;
            const avatarUrl = node.photo ? node.photo : (isImgPath(node.emoji) ? getAssetUrl(node.emoji) : null);
            const clipId = 'clip-avatar-' + node.id;
            const rowIsCrowded = node.y < 340;
            const nameFontSize = rowIsCrowded ? 13 : 18;
            const relFontSize = rowIsCrowded ? 10 : 13;
            const maxNameChars = rowIsCrowded ? 14 : (node.y < 620 ? 18 : 24);
            const maxRelChars = rowIsCrowded ? 13 : (node.y < 620 ? 16 : 20);
            const displayName = truncateText(node.name, maxNameChars);
            const displayRel = truncateText(rel.label || node.rel, maxRelChars);

            nodesMarkup += '<g class="cursor-pointer" onclick="setSelectedMember(' + node.id + ')">';
            nodesMarkup += '<circle cx="' + (node.x + offsetX) + '" cy="' + (node.y + offsetY) + '" r="55" fill="#ffffff" stroke="' + rel.stroke + '" stroke-width="' + ringWidth + '" />';

            if (avatarUrl) {
                nodesMarkup += '<defs><clipPath id="' + clipId + '"><circle cx="' + (node.x + offsetX) + '" cy="' + (node.y + offsetY) + '" r="48" /></clipPath></defs>';
                nodesMarkup += '<image href="' + avatarUrl + '" x="' + ((node.x + offsetX) - 48) + '" y="' + ((node.y + offsetY) - 48) + '" width="96" height="96" preserveAspectRatio="xMidYMid slice" clip-path="url(#' + clipId + ')" />';
            } else {
                nodesMarkup += '<circle cx="' + (node.x + offsetX) + '" cy="' + (node.y + offsetY) + '" r="48" fill="' + rel.color + '" />';
                nodesMarkup += '<text x="' + (node.x + offsetX) + '" y="' + ((node.y + offsetY) + 10) + '" text-anchor="middle" font-size="28" fill="#334155" font-family="Nunito, sans-serif">' + esc(node.emoji || '?') + '</text>';
            }

            nodesMarkup += '<text x="' + (node.x + offsetX) + '" y="' + ((node.y + offsetY) + 86) + '" text-anchor="middle" font-size="' + nameFontSize + '" font-weight="700" fill="#1e3a5f" font-family="Nunito, sans-serif">' + esc(displayName) + '</text>';
            nodesMarkup += '<text x="' + (node.x + offsetX) + '" y="' + ((node.y + offsetY) + 110) + '" text-anchor="middle" font-size="' + relFontSize + '" font-weight="700" fill="' + rel.stroke + '" font-family="Nunito, sans-serif">' + esc(displayRel) + '</text>';
            nodesMarkup += '</g>';
        });

        svg.setAttribute('viewBox', '0 0 ' + TREE_BASE_W + ' ' + TREE_BASE_H);
        svg.setAttribute('width', String(TREE_BASE_W * treeZoom));
        svg.setAttribute('height', String(TREE_BASE_H * treeZoom));
        svg.innerHTML = '<rect x="0" y="0" width="' + TREE_BASE_W + '" height="' + TREE_BASE_H + '" fill="#f8fbff"/>' + linksMarkup + nodesMarkup;

        updateZoomLabel();
        autoCenterTree();
    }

    function autoCenterTree(force) {
        const scroll = document.getElementById('tree-scroll');
        if (!scroll) {
            return;
        }

        if (!force && hasAutoCentered) {
            return;
        }

        const targetLeft = Math.max(0, (scroll.scrollWidth - scroll.clientWidth) / 2);
        const targetTop = Math.max(0, (scroll.scrollHeight - scroll.clientHeight) / 2);
        scroll.scrollTo({ left: targetLeft, top: targetTop, behavior: 'auto' });
        hasAutoCentered = true;
    }

    function updateZoomLabel() {
        const label = document.getElementById('zoom-label');
        if (label) {
            label.textContent = Math.round(treeZoom * 100) + '%';
        }
    }

    function setZoom(next) {
        treeZoom = Math.max(0.5, Math.min(2.2, next));
        renderTree();
    }

    function zoomIn() { setZoom(treeZoom + 0.1); }
    function zoomOut() { setZoom(treeZoom - 0.1); }
    function zoomReset() {
        hasAutoCentered = false;
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

        popup.document.write('<html><head><title>Ургийн мод хэвлэх</title></head><body style="margin:0;display:flex;justify-content:center;align-items:center;background:#fff;">' + svg.outerHTML + '</body></html>');
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

    function setFormModeCreate() {
        editingMemberId = null;
        const form = document.getElementById('add-form');
        if (!form) {
            return;
        }

        form.action = '/family-tree';
        const override = form.querySelector('input[name="_method"]');
        if (override) {
            override.remove();
        }

        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.textContent = 'Хадгалах';
        }
    }

    function setFormModeEdit(memberId) {
        editingMemberId = memberId;
        const form = document.getElementById('add-form');
        if (!form) {
            return;
        }

        form.action = '/family-tree/' + memberId;
        let override = form.querySelector('input[name="_method"]');
        if (!override) {
            override = document.createElement('input');
            override.type = 'hidden';
            override.name = '_method';
            form.appendChild(override);
        }
        override.value = 'PATCH';

        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.textContent = 'Засвар хадгалах';
        }
    }

    function openAddModal() {
        setFormModeCreate();
        const relatedInput = getRelatedToInput();
        if (relatedInput) {
            relatedInput.value = '';
        }

        const modal = document.getElementById('add-modal');
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAddModal(event) {
        const modal = document.getElementById('add-modal');
        if (!modal) {
            return;
        }

        if (event && event.target !== modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditModal() {
        const member = getSelectedMember();
        if (!member) {
            openAddModal();
            return;
        }

        setFormModeEdit(member.id);
        document.querySelector('[name="name"]').value = member.name || '';
        document.querySelector('[name="rel"]').value = member.rel || '';
        document.querySelector('[name="bio"]').value = member.bio || '';

        const relatedInput = getRelatedToInput();
        if (relatedInput) {
            relatedInput.value = member.related_to_id || '';
        }

        const modal = document.getElementById('add-modal');
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openAddByRelation(rel) {
        setFormModeCreate();
        document.querySelector('[name="rel"]').value = rel;

        const selected = getSelectedMember() || getMeMember();
        const relatedInput = getRelatedToInput();
        if (relatedInput) {
            if (rel === 'partner' || rel === 'child') {
                const owner = getBaseDescendantOwner(selected);
                relatedInput.value = owner ? owner.id : '';
            } else {
                relatedInput.value = '';
            }
        }

        const modal = document.getElementById('add-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        document.querySelector('[name="name"]').focus();
    }

    function addChildWithPartnerCheck() {
        const selected = getSelectedMember() || getMeMember();
        const owner = getBaseDescendantOwner(selected);
        if (!owner) {
            return;
        }

        if (!findPartnerFor(owner.id)) {
            alert('Эхлээд хань нэмнэ үү.');
            openAddByRelation('partner');
            return;
        }

        openAddByRelation('child');
    }

    function getAutoParentPlan(member) {
        if (!member) {
            return [];
        }

        const relatedToId = member.id;

        if (member.rel === 'dad') {
            return [
                { rel: 'gpl', name: 'Аавын аав', emoji: 'image/hogshin_aaw.png', bio: 'Өвөө', related_to_id: relatedToId },
                { rel: 'gml', name: 'Аавын ээж', emoji: 'image/emee.png', bio: 'Эмээ', related_to_id: relatedToId },
            ];
        }

        if (member.rel === 'mom') {
            return [
                { rel: 'gpr', name: 'Ээжийн аав', emoji: 'image/hogshin_aaw.png', bio: 'Өвөө', related_to_id: relatedToId },
                { rel: 'gmr', name: 'Ээжийн ээж', emoji: 'image/emee.png', bio: 'Эмээ', related_to_id: relatedToId },
            ];
        }

        if (member.rel === 'gpl') {
            return [
                { rel: 'ggplf', name: 'Аавын аавын аав', emoji: 'image/hogshin_aaw.png', bio: 'Өвөөгийн аав', related_to_id: relatedToId },
                { rel: 'ggplm', name: 'Аавын аавын ээж', emoji: 'image/emee.png', bio: 'Өвөөгийн ээж', related_to_id: relatedToId },
            ];
        }

        if (member.rel === 'gml') {
            return [
                { rel: 'ggmlf', name: 'Аавын ээжийн аав', emoji: 'image/hogshin_aaw.png', bio: 'Эмээгийн аав', related_to_id: relatedToId },
                { rel: 'ggmlm', name: 'Аавын ээжийн ээж', emoji: 'image/emee.png', bio: 'Эмээгийн ээж', related_to_id: relatedToId },
            ];
        }

        if (member.rel === 'gpr') {
            return [
                { rel: 'ggprf', name: 'Ээжийн аавын аав', emoji: 'image/hogshin_aaw.png', bio: 'Өвөөгийн аав', related_to_id: relatedToId },
                { rel: 'ggprm', name: 'Ээжийн аавын ээж', emoji: 'image/emee.png', bio: 'Өвөөгийн ээж', related_to_id: relatedToId },
            ];
        }

        if (member.rel === 'gmr') {
            return [
                { rel: 'ggmrf', name: 'Ээжийн ээжийн аав', emoji: 'image/hogshin_aaw.png', bio: 'Эмээгийн аав', related_to_id: relatedToId },
                { rel: 'ggmrm', name: 'Ээжийн ээжийн ээж', emoji: 'image/emee.png', bio: 'Эмээгийн ээж', related_to_id: relatedToId },
            ];
        }

        return [
            { rel: 'dad', name: 'Миний аав', emoji: 'image/er_hun.png', bio: 'Аав', related_to_id: relatedToId },
            { rel: 'mom', name: 'Миний ээж', emoji: 'image/eej.png', bio: 'Ээж', related_to_id: relatedToId },
        ];
    }

    function getMissingAutoParents(plan) {
        return plan.filter((entry) => !members.some((member) => member.rel === entry.rel));
    }

    function createLocalMember(payload) {
        const nextId = Math.max(...members.map((item) => item.id), 0) + 1;
        const created = {
            id: nextId,
            name: payload.name,
            rel: payload.rel,
            related_to_id: toId(payload.related_to_id),
            emoji: payload.emoji,
            bio: payload.bio,
            photo: null,
        };

        members.push(created);
        return created;
    }

    async function createServerMember(payload) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const body = new FormData();
        body.append('_token', token);
        body.append('name', payload.name);
        body.append('rel', payload.rel);
        body.append('emoji', payload.emoji);
        body.append('bio', payload.bio);

        if (toId(payload.related_to_id)) {
            body.append('related_to_id', String(toId(payload.related_to_id)));
        }

        const response = await fetch('/family-tree', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        });

        if (!response.ok) {
            throw new Error('failed_to_create_parent');
        }
    }

    async function addParentsAuto() {
        const selected = getSelectedMember() || getMeMember();
        if (!selected) {
            return;
        }

        const plan = getAutoParentPlan(selected);
        const missing = getMissingAutoParents(plan);

        if (missing.length === 0) {
            alert('Эцэг эх аль хэдийн нэмэгдсэн байна.');
            return;
        }

        if (IS_AUTH) {
            try {
                for (const payload of missing) {
                    await createServerMember(payload);
                }
                window.location.reload();
                return;
            } catch (error) {
                alert('Эцэг эх нэмэх үед алдаа гарлаа. Дахин оролдоно уу.');
                return;
            }
        }

        const created = missing.map((payload) => createLocalMember(payload));

        try {
            localStorage.setItem('fm_members', JSON.stringify(members));
        } catch (error) {
            // ignore localStorage errors
        }

        if (created.length > 0) {
            hasAutoCentered = false;
            setSelectedMember(created[0].id);
        } else {
            hasAutoCentered = false;
            renderTree();
        }
    }

    function deleteLocal() {
        if (!selectedMemberId) {
            return;
        }
        if (!confirm('Энэ хүнийг устгах уу?')) {
            return;
        }

        members = members.filter((item) => item.id !== selectedMemberId);

        try {
            localStorage.setItem('fm_members', JSON.stringify(members));
        } catch (error) {
            // ignore localStorage errors
        }

        if (members.length > 0) {
            const fallback = members.find((item) => item.rel === 'me') || members[0];
            hasAutoCentered = false;
            setSelectedMember(fallback.id);
        } else {
            selectedMemberId = null;
            document.getElementById('sel-name').textContent = 'Сонгогдоогүй';
            document.getElementById('sel-rel').textContent = '-';
            document.getElementById('sel-name-meta').textContent = '-';
            document.getElementById('sel-rel-meta').textContent = '-';
            document.getElementById('sel-bio-meta').textContent = '-';
            document.getElementById('sel-av-area').innerHTML = '';
            hasAutoCentered = false;
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
                document.getElementById('upload-inner').innerHTML = '<img class="mx-auto h-20 w-20 rounded-full border-4 border-green-500 object-cover" src="' + photoData + '"/><div class="mt-1 text-center text-xs font-black text-green-600">Бэлэн</div>';
            };
            reader.readAsDataURL(file);
            return;
        }

        const tempUrl = URL.createObjectURL(file);
        document.getElementById('upload-inner').innerHTML = '<img class="mx-auto h-20 w-20 rounded-full border-4 border-green-500 object-cover" src="' + tempUrl + '"/><div class="mt-1 text-center text-xs font-black text-green-600">Бэлэн</div>';
    }

    function handleAddSubmit(event) {
        if (IS_AUTH) {
            return true;
        }

        event.preventDefault();

        const nameInput = document.querySelector('[name="name"]');
        const relInput = document.querySelector('[name="rel"]');
        const bioInput = document.querySelector('[name="bio"]');
        const relatedInput = getRelatedToInput();
        const submitButton = document.querySelector('#add-form button[type="submit"]');

        const selected = getSelectedMember() || getMeMember();
        const owner = getBaseDescendantOwner(selected);

        const name = (nameInput?.value || '').trim();
        const rel = relInput?.value || '';
        const bio = (bioInput?.value || '').trim();
        let relatedToId = toId(relatedInput ? relatedInput.value : null);

        if ((rel === 'partner' || rel === 'child') && !relatedToId) {
            relatedToId = owner ? owner.id : null;
            if (relatedInput) {
                relatedInput.value = relatedToId || '';
            }
        }

        let errorMessage = '';
        if (!name || !rel) {
            errorMessage = 'Нэр ба харилцаагаа оруулна уу';
        } else if (rel === 'me' && members.some((item) => item.rel === 'me' && item.id !== editingMemberId)) {
            errorMessage = 'Зөвхөн нэг "Би" байна';
        } else if (rel === 'partner' && !relatedToId) {
            errorMessage = 'Хань нэмэх хүнээ сонгоно уу';
        } else if (rel === 'partner' && members.some((item) => item.rel === 'partner' && toId(item.related_to_id) === relatedToId && item.id !== editingMemberId)) {
            errorMessage = 'Энэ хүнд хань аль хэдийн байна';
        } else if (rel === 'child' && !relatedToId) {
            errorMessage = 'Хүүхдийг аль хүнд холбохоо сонгоно уу';
        }

        if (errorMessage) {
            if (submitButton) {
                submitButton.textContent = errorMessage;
                submitButton.classList.remove('bg-green-600', 'hover:bg-green-700');
                submitButton.classList.add('bg-red-600');

                setTimeout(() => {
                    submitButton.textContent = editingMemberId ? 'Засвар хадгалах' : 'Хадгалах';
                    submitButton.classList.remove('bg-red-600');
                    submitButton.classList.add('bg-green-600', 'hover:bg-green-700');
                }, 1800);
            }
            return false;
        }

        if (editingMemberId) {
            const editing = getMemberById(editingMemberId);
            if (!editing) {
                return false;
            }

            editing.name = name;
            editing.rel = rel;
            editing.related_to_id = relatedToId;
            editing.bio = bio || 'Гэр бүлийн гишүүн';
            editing.emoji = selEmoji;
            editing.photo = photoData || editing.photo || null;

            try {
                localStorage.setItem('fm_members', JSON.stringify(members));
            } catch (error) {
                // ignore localStorage errors
            }

            setSelectedMember(editing.id);
            closeAddModal();
            setFormModeCreate();
            return false;
        }

        const nextId = Math.max(...members.map((item) => item.id), 0) + 1;
        members.push({ id: nextId, name, rel, related_to_id: relatedToId, emoji: selEmoji, bio: bio || 'Гэр бүлийн гишүүн', photo: photoData });

        try {
            localStorage.setItem('fm_members', JSON.stringify(members));
        } catch (error) {
            // ignore localStorage errors
        }

        if (nameInput) {
            nameInput.value = '';
        }
        if (relInput) {
            relInput.value = '';
        }
        if (bioInput) {
            bioInput.value = '';
        }
        if (relatedInput) {
            relatedInput.value = '';
        }

        photoData = null;
        selEmoji = 'image/jaal_huu.png';
        document.getElementById('fi-emoji').value = selEmoji;

        document.querySelectorAll('#emoji-row button').forEach((item, index) => {
            item.classList.remove('border-green-500', 'bg-green-100');
            item.classList.add(index === 0 ? 'border-green-500' : 'border-transparent');
            item.classList.add(index === 0 ? 'bg-green-100' : 'bg-gray-100');
        });

        document.getElementById('upload-inner').innerHTML = '<div class="text-lg font-bold text-slate-600">Зураг сонгох</div><div class="mt-1 text-xs text-gray-400">Дарж оруулна уу</div>';

        if (submitButton) {
            submitButton.textContent = 'Хадгалагдлаа';
            submitButton.classList.remove('bg-green-600', 'hover:bg-green-700');
            submitButton.classList.add('bg-orange-500');

            setTimeout(() => {
                submitButton.textContent = 'Хадгалах';
                submitButton.classList.remove('bg-orange-500');
                submitButton.classList.add('bg-green-600', 'hover:bg-green-700');
            }, 1400);
        }

        hasAutoCentered = false;
        setSelectedMember(nextId);
        closeAddModal();
        return false;
    }

    window.setSelectedMember = setSelectedMember;
    window.zoomIn = zoomIn;
    window.zoomOut = zoomOut;
    window.zoomReset = zoomReset;
    window.printTree = printTree;
    window.downloadTreePng = downloadTreePng;
    window.openAddModal = openAddModal;
    window.closeAddModal = closeAddModal;
    window.openEditModal = openEditModal;
    window.openAddByRelation = openAddByRelation;
    window.addChildWithPartnerCheck = addChildWithPartnerCheck;
    window.addParentsAuto = addParentsAuto;
    window.deleteLocal = deleteLocal;
    window.pickEmoji = pickEmoji;
    window.previewImg = previewImg;
    window.handleAddSubmit = handleAddSubmit;

    loadMembers();
    if (members.length > 0) {
        const startMember = members.find((item) => item.rel === 'me') || members[0];
        setSelectedMember(startMember.id);
    } else {
        renderTree();
    }

    if (HAS_ERRORS) {
        openAddModal();
    }
})();

