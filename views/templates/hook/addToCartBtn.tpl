<div class="custom-addToCart">
    {widget name="dioqaapiconnexion" action="displayBooked" id_product=$product.id}
    <button class="btn btn-primary add-to-cart btn-lg btn-block btn-add-to-cart js-add-to-cart ml-2"
        data-button-action-custom="add-to-cart" onclick="handleAddToCart()" type="button" {if !$product.add_to_cart_url}
        disabled {/if}>
        <span class="btn-add-to-cart__spinner" role="status" aria-hidden="true"></span>
        {l s='Add to cart' d='Shop.Theme.Actions'}
    </button>
</div>