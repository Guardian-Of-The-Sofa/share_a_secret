import $ from 'jquery';

function requireAll(require) {
    require.keys().forEach(require);
}

requireAll(require.context('./Resources/Private/', true, /^[^_]+\.scss$/));
$(() => requireAll(require.context('./Resources/Private/', true, /^[^_]+\.js$/)));
