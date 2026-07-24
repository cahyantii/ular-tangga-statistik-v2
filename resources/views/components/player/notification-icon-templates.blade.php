{{--
    Template icon notifikasi (dikloning oleh resources/js/notifications.js ke
    setiap baris dropdown) - sama seperti pola template icon achievement di
    game/show.blade.php: memastikan bell dropdown pakai SVG asli <x-player.icon>
    yang sama dipakai di seluruh aplikasi, bukan re-implementasi terpisah di JS.
--}}
<div id="notification-icon-templates" class="hidden">
    <template data-icon="bell"><x-player.icon name="bell" class="h-4 w-4" /></template>
    <template data-icon="trophy"><x-player.icon name="trophy" class="h-4 w-4" /></template>
    <template data-icon="close"><x-player.icon name="close" class="h-4 w-4" /></template>
    <template data-icon="medal"><x-player.icon name="medal" class="h-4 w-4" /></template>
    <template data-icon="wifi"><x-player.icon name="wifi" class="h-4 w-4" /></template>
    <template data-icon="hourglass"><x-player.icon name="hourglass" class="h-4 w-4" /></template>
    <template data-icon="users"><x-player.icon name="users" class="h-4 w-4" /></template>
    <template data-icon="gamepad"><x-player.icon name="gamepad" class="h-4 w-4" /></template>
    <template data-icon="upload-cloud"><x-player.icon name="upload-cloud" class="h-4 w-4" /></template>
    <template data-icon="download"><x-player.icon name="download" class="h-4 w-4" /></template>
    <template data-icon="mail"><x-player.icon name="mail" class="h-4 w-4" /></template>
    <template data-icon="user-plus"><x-player.icon name="user-plus" class="h-4 w-4" /></template>
    <template data-icon="check-circle"><x-player.icon name="check-circle" class="h-4 w-4" /></template>
</div>
