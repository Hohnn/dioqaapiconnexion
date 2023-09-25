<!-- Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingDetailModal"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{Context::getContext()->link->getModuleLink('dioqaapiconnexion', 'bookingExpiredCart')}"
                method="post">
                <div class="modal-header">
                    <h4 class="modal-title" id="bookingDetailModal">Votre panier à expiré</h4>
                </div>
                {if $products && $products|count}
                    <div class="modal-body">
                        <p class="body-title">C'est produits sont encore disponible !</p>
                    <ul class="row">
                        {foreach from=$products key=key item=product}
                        <li class="col-12">

                            <div class="product">
                                <img src="{$link->getImageLink($product['link_rewrite'], $product['id_image'], 'small_default')|escape:'html':'UTF-8'}"
                                    alt="{$product['name']}" class="img-fluid">

                                <div>
                                    <span class="name d-block">{$product['name']}</span>
                                    <span class="price">{Tools::displayPrice($product['price'])}</span>
                                </div>
                            </div>

                            <div class="actions">
                                <div>
                                    <input class="close-radio" type="radio" id="set{$key}"
                                        name="book_product_{$product['id_product']}_customization_{$product['id_customization']}"
                                        value="true" checked>
                                    <label class="btn btn-primary" for="set{$key}">Réserver</label>
                                </div>

                                <div>
                                    <input class="close-radio" type="radio" id="close{$key}"
                                        name="book_product_{$product['id_product']}_customization_{$product['id_customization']}"
                                        value="false">
                                    <label class="close-label" for="close{$key}">
                                        <span class="material-icons">close</span>
                                    </label>
                                </div>
                            </div>
                        </li>
                        {/foreach}
                    </ul>
                </div>
                {else}
                <div class="modal-body">
                    <p class="body-title">Vos produit ne sont plus disponible</p>
                    <p class="body-text">Vous pouvez les retrouver dans les catégories suivantes :</p>
                    <ul class="row">
                        {foreach from=$productCats key=key item=cat}
                        <li class="col-12">
                            <span class="name">{$cat.name}</span>
                            <a href="{$cat.link}" class="btn btn-primary">Voir</a>
                        </li>
                        {/foreach}
                    </ul>
                </div>
                {/if}
                <div class="modal-footer">
                    {if $products && $products|count}
                    <button type="submit" name="bookingExpiredCart" class="btn btn-primary">Valider</button>
                    {else}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    {/if}

                </div>
            </form>
        </div>
    </div>
</div>