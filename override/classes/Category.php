<?php

class Category extends CategoryCore
{
    public function update($nullValues = false)
    {
        if ($this->id_parent == $this->id) {
            throw new PrestaShopException('a category cannot be its own parent');
        }

        /* if ($this->is_root_category && $this->id_parent != (int) Configuration::get('PS_ROOT_CATEGORY')) {
            $this->is_root_category = 0;
        } */

        // Update group selection
        $this->updateGroup($this->groupBox);

        if ($this->level_depth != $this->calcLevelDepth()) {
            $this->level_depth = $this->calcLevelDepth();
            $changed = true;
        }

        // If the parent category was changed, we don't want to have 2 categories with the same position
        if (!isset($changed)) {
            $changed = $this->getDuplicatePosition();
        }

        if ($changed) {
            if (Tools::isSubmit('checkBoxShopAsso_category')) {
                foreach (Tools::getValue('checkBoxShopAsso_category') as $idAssoObject => $idShop) {
                    $this->addPosition($this->position, (int) $idShop);
                }
            } else {
                foreach (Shop::getShops(true) as $shop) {
                    $this->addPosition($this->position, $shop['id_shop']);
                }
            }
        }

        $ret = parent::update($nullValues);
        if ($changed && !$this->doNotRegenerateNTree) {
            $this->cleanPositions((int) $this->id_parent);
            Category::regenerateEntireNtree();
            $this->recalculateLevelDepth($this->id);
        }
        Hook::exec('actionCategoryUpdate', ['category' => $this]);

        return $ret;
    }
}
