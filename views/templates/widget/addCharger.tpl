<div class="chargeur">
    <form action="{Context::getContext()->link->getModuleLink('dioqaapiconnexion', 'addCharger')}" method="POST">
        <input type="hidden" name="id_product" value="{$product.id_product}">
        {if is_array($product.customizations) && $product.customizations|count && $product.customizations[0].fields[0]['text'] == "Avec chargeur"}
        <button type="submit" name="removeCharger">
            <span>Supprimer le chargeur</span>
        </button>
        {else}
        <button type="submit" name="addCharger">
            <img src="themes/classic-rocket-child/assets/img/chargeur.svg">
            <span>Ajouter un bloc de charge pour 5€ de plus</span>
        </button>
        {/if}
    </form>
</div>