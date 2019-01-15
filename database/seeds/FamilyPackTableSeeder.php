<?php

use App\Models\FamilyPack;
use App\Models\User;

/**
 * Class FamilyPackTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 */
class FamilyPackTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->seedFromFactory(FamilyPack::class, 4);

        // Fill half of them
        $familyPacksFull = FamilyPack::limit(2)->get();

        /** @var FamilyPack $familyPack */
        foreach ($familyPacksFull as $familyPack) {
            $children = User::whereNull("family_pack_id")
                ->limit($familyPack->children_count)
                ->get();

            foreach ($children as $child) {
                $child->family_pack_id = $familyPack->id;
                $child->save();
            }
        }
    }
}
