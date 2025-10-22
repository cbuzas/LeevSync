<div
    x-data="{
        notifications: [],
        nextId: 0,
        add(message, type = 'info', duration = 5000) {
            const id = this.nextId++;
            const notification = { id, message, type, duration, visible: false };
            this.notifications.push(notification);

            // Trigger fade-in on next tick
            setTimeout(() => {
                const notif = this.notifications.find(n => n.id === id);
                if (notif) notif.visible = true;
            }, 10);

            if (duration > 0) {
                setTimeout(() => {
                    this.remove(id);
                }, duration);
            }
        },
        remove(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                // Trigger fade-out
                this.notifications[index].visible = false;

                // Remove from array after transition completes
                setTimeout(() => {
                    const idx = this.notifications.findIndex(n => n.id === id);
                    if (idx > -1) {
                        this.notifications.splice(idx, 1);
                    }
                }, 250); // Slightly longer than the 200ms leave transition
            }
        },
        getIcon(type) {
            const icons = {
                success: '<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;1.5&quot; stroke=&quot;currentColor&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z&quot; /></svg>',
                error: '<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;1.5&quot; stroke=&quot;currentColor&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z&quot; /></svg>',
                warning: '<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;1.5&quot; stroke=&quot;currentColor&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z&quot; /></svg>',
                info: '<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;1.5&quot; stroke=&quot;currentColor&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z&quot; /></svg>',
            };
            return icons[type] || icons.info;
        },
        getStyles(type) {
            const styles = {
                success: 'bg-green-50 dark:bg-green-900/50 text-green-800 dark:text-green-200 border-green-200 dark:border-green-800',
                error: 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200 border-red-200 dark:border-red-800',
                warning: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 border-yellow-200 dark:border-yellow-800',
                info: 'bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200 border-blue-200 dark:border-blue-800',
            };
            return styles[type] || styles.info;
        }
    }"
    @notify.window="add($event.detail.message, $event.detail.type || 'info', $event.detail.duration || 5000)"
    class="fixed top-5 right-8.5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none"
    role="status"
    aria-live="polite"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-show="notification.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-full"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-full"
            :class="getStyles(notification.type)"
            class="flex items-start gap-3 p-4 rounded-lg border shadow-lg pointer-events-auto"
        >
            <div class="flex-shrink-0" x-html="getIcon(notification.type)"></div>
            <div class="flex-1 text-sm font-medium" x-text="notification.message"></div>
            <button
                @click="remove(notification.id)"
                type="button"
                class="flex-shrink-0 cursor-pointer text-current opacity-50 hover:opacity-100 transition-opacity"
                aria-label="Close notification"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
