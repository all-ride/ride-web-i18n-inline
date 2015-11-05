
<form class="inline__translator popup" id="popup-translation">
    <h3 class="popup-title">Edit [{$key}]</h3>

    <div class="popup-body">
        {foreach $translations as $translation}
            <label for="{$translation['key']}:{$translation['code']}">{$translation['locale']} :</label>
            <input type="text"
                id="{$translation['key']}:{$translation['code']}"
                value="{$translation['translation']}"
                class="translation-popup-data"
                data-locale="{$translation['code']}"
                data-key="{$translation['key']}"
                name="{$translation['code']}"/>
        {/foreach}
    </div>

    <div class="popup-actions">
        <button id="save-translation" class="btn btn--brand">Save</button>
        <button id="cancel-translation" class="btn">Cancel</button>
    </div>
</form>
