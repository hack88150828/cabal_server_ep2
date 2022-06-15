<?php

namespace App\Http\Controllers;

use App\Http\Requests\CharacterUpdateRequest;
use App\Models\Character;
use App\Models\Dictionaries\CharacterNationDictionary;
use App\Models\Dictionaries\ItemDictionary;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function index(Character $character): View
    {
        $character->load('inventory');

        $closedClots = [];

        foreach ($character->inventory->Data as $item) {
            $closedClots = array_replace($closedClots, $item->closed_positions);
        }

        return view('character.index', compact('character', 'closedClots'));
    }

    public function edit(Character $character): View
    {
        $characterNations = CharacterNationDictionary::getDictionary();

        return view('character.edit', compact('character', 'characterNations'));
    }

    public function update(Character $character, CharacterUpdateRequest $request): RedirectResponse
    {
        $character->update($request->validated());

        return redirect()->route('character.index', $character);
    }

    public function editItem(Character $character, $position): View
    {
        $item = $character->inventory->Data->firstWhere('dec_position', '=', $position);

        if (!$item) {
            $item = new Item($position);
        }

        $items = ItemDictionary::getDictionary();

        return view('character.inventory.edit', compact('item', 'character', 'items'));
    }

    public function saveItem(Character $character, $position, Request $request): RedirectResponse
    {
        $item = $character->inventory->Data->firstWhere('dec_position', '=', $position) ?? new Item($position);

        $item->update($request->except(['_token']));

        if (!$item->exist) {
            $character->inventory->Data->push($item);
        }

        $character->inventory->save();

        return redirect()->route('character.index', $character);
    }
}
