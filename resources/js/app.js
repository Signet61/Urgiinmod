import './bootstrap';

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

// Expose so page-level scripts can re-init after dynamic DOM changes
window.createLucideIcons = () => createIcons({ icons });

Alpine.start();

// ES modules are deferred — DOM is ready at this point
window.createLucideIcons();
