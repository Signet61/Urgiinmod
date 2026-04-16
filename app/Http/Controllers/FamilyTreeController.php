<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FamilyTreeController extends Controller
{
    public function index()
    {
        $members = auth()->check()
            ? auth()->user()->familyMembers()->orderBy('id')->get()
            : collect();

        return view('family-tree', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'rel' => 'required|string|in:gpl,gml,gpr,gmr,ggplf,ggplm,ggmlf,ggmlm,ggprf,ggprm,ggmrf,ggmrm,dad,mom,uncle,aunt,sib,me,cousin,partner,child',
            'related_to_id' => 'nullable|integer',
            'emoji' => 'nullable|string',
            'bio' => 'nullable|string|max:300',
            'photo' => 'nullable|image|max:2048',
        ]);

        $relatedToId = null;
        if ($request->filled('related_to_id')) {
            $relatedToId = auth()->user()->familyMembers()->whereKey($request->integer('related_to_id'))->value('id');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('family-photos', 'public');
        }

        auth()->user()->familyMembers()->create([
            'name' => $request->name,
            'rel' => $request->rel,
            'related_to_id' => $relatedToId,
            'emoji' => $request->emoji ?? 'image/jaal_huu.png',
            'bio' => $request->bio,
            'photo' => $photoPath,
        ]);

        return redirect()->route('family-tree')->with('success', $request->name.' нэмэгдлээ!');
    }

    public function update(Request $request, FamilyMember $familyMember)
    {
        abort_if($familyMember->user_id !== auth()->id(), 403);

        $request->validate([
            'name' => 'required|string|max:100',
            'rel' => 'required|string|in:gpl,gml,gpr,gmr,ggplf,ggplm,ggmlf,ggmlm,ggprf,ggprm,ggmrf,ggmrm,dad,mom,uncle,aunt,sib,me,cousin,partner,child',
            'related_to_id' => 'nullable|integer',
            'emoji' => 'nullable|string',
            'bio' => 'nullable|string|max:300',
            'photo' => 'nullable|image|max:2048',
        ]);

        $relatedToId = null;
        if ($request->filled('related_to_id')) {
            $relatedToId = auth()->user()->familyMembers()->whereKey($request->integer('related_to_id'))->value('id');
        }

        $data = [
            'name' => $request->name,
            'rel' => $request->rel,
            'related_to_id' => $relatedToId,
            'emoji' => $request->input('emoji') ?: $familyMember->emoji,
            'bio' => $request->bio,
        ];

        if ($request->hasFile('photo')) {
            if ($familyMember->photo) {
                Storage::disk('public')->delete($familyMember->photo);
            }
            $data['photo'] = $request->file('photo')->store('family-photos', 'public');
        }

        $familyMember->update($data);
        $familyMember->refresh();

        return response()->json([
            'member' => [
                'id' => $familyMember->id,
                'name' => $familyMember->name,
                'rel' => $familyMember->rel,
                'related_to_id' => $familyMember->related_to_id,
                'emoji' => $familyMember->emoji,
                'photo' => $familyMember->photo ? asset('storage/'.$familyMember->photo) : null,
                'bio' => $familyMember->bio,
            ],
        ]);
    }

    public function destroy(FamilyMember $familyMember)
    {
        abort_if($familyMember->user_id !== auth()->id(), 403);

        if ($familyMember->photo) {
            Storage::disk('public')->delete($familyMember->photo);
        }

        $familyMember->delete();

        return redirect()->route('family-tree')->with('success', 'Гишүүн устгагдлаа.');
    }
}
