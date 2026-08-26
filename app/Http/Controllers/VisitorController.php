<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $query = Visitor::query();

        if ($request->has('range') && $request->range !== 'all') {
            $now = now();
            switch ($request->range) {
                case '1h':  $query->where('created_at', '>=', $now->subHour()); break;
                case '24h': $query->where('created_at', '>=', $now->subHours(24)); break;
                case '7d':  $query->where('created_at', '>=', $now->subDays(7)); break;
                case '30d': $query->where('created_at', '>=', $now->subDays(30)); break;
            }
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('id_number', 'like', '%' . $request->search . '%');
            });
        }

        $visitors = $query->orderByRaw("status = 'Inside' DESC")
                          ->orderBy('created_at', 'desc')
                          ->get();

        $totalVisitors = Visitor::count();
        $currentlyInside = Visitor::where('status', 'Inside')->count();
        $totalExits = Visitor::where('status', 'Checked Out')->count();

        return view('admin.visitor_index', compact('visitors', 'totalVisitors', 'currentlyInside', 'totalExits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'person_to_meet' => 'required|string|max:255',
            'phone'          => 'required|numeric|digits_between:10,15',
            'purpose'        => 'required|string|max:255',
            'id_type'        => 'required|string',
            'id_number'      => 'required|string',
            'visitor_photo'  => 'required|string', 
        ]);

        $photoPath = null;

        if ($request->visitor_photo) {
            $image_parts = explode(";base64,", $request->visitor_photo);
            $image_base64 = base64_decode($image_parts[1]);
            
            $fileName = 'visitors/vis_' . time() . '_' . Str::random(5) . '.jpg';
            
            // FIXED: Removed the 'public' ACL flag here to comply with AWS S3 strict security
            Storage::disk('s3')->put($fileName, $image_base64);
            
            $photoPath = $fileName; 
        }

        Visitor::create([
            'name'           => $request->name,
            'company'        => $request->company,
            'person_to_meet' => $request->person_to_meet,
            'purpose'        => $request->purpose,
            'phone'          => $request->phone,
            'id_type'        => $request->id_type,
            'id_number'      => $request->id_number, 
            'photo_path'     => $photoPath, 
            'check_in_time'  => Carbon::now(),
            'status'         => 'Inside',
        ]);

        return redirect()->route('admin.visitor_index')->with('success', 'Visitor checked in successfully & Photo saved securely to S3!');
    }

    public function checkout($id)
    {
        $visitor = Visitor::find($id);

        if ($visitor) {
            $visitor->update([
                'check_out_time' => Carbon::now(), 
                'status'         => 'Checked Out'
            ]);

            return redirect()->back()->with('success', 'Visitor checked out successfully!');
        }

        return redirect()->back()->with('error', 'Visitor not found.');
    }

    public function destroy($id)
    {
        $visitor = Visitor::findOrFail($id);

        if ($visitor->photo_path && Storage::disk('s3')->exists($visitor->photo_path)) {
            Storage::disk('s3')->delete($visitor->photo_path);
        }

        $visitor->delete();

        return redirect()->back()->with('success', 'Visitor record and photo permanently deleted.');
    }

    public function downloadReport()
    {
        $visitors = Visitor::orderBy('created_at', 'desc')->get();
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Visitor_Report_'.date('Y-m-d').'.xls"');

        echo "Visitor Name\tPhone Number\tCompany\tPerson to Meet\tPurpose\tID Type\tID Number\tCheck In\tCheck Out\tStatus\n";
        
        foreach ($visitors as $v) {
            $checkOut = $v->check_out_time ?? '---';
            echo "{$v->name}\t{$v->phone}\t{$v->company}\t{$v->person_to_meet}\t{$v->purpose}\t{$v->id_type}\t{$v->id_number}\t{$v->check_in_time}\t{$checkOut}\t{$v->status}\n";
        }
        exit;
    }
}