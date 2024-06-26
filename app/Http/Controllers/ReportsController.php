<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Reports;
use Illuminate\View\View;
use App\Models\Spot;
use App\Models\Year;
use Illuminate\Support\Facades\Auth;


class ReportsController extends Controller
{
    public function create(): View
    {
        return view('report.report');
    }

    public function viewReport()
    {
        // Mengambil 8 laporan per halaman
        $reports = Reports::paginate(8);
        return view('report.viewreport', compact('reports'));
    }

    // Tambahkan method filter untuk mengatur filter
    public function filter($category)
    {
        // Mengambil 10 laporan per halaman berdasarkan kategori
        $reports = Reports::where('category', $category)->paginate(8);
        return view('report.viewreport', compact('reports'));
    }

    public function store(Request $request){
        $request->validate([ 
            'report_title' => 'required|string|max:255', 
            'category' => 'required|string|max:255', 
            'date' => 'required|date', 
            'description' => 'required|string',
            'coordinates' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Get the uploaded image
        $image = $request->file('image');

        // Determine fillColor based on category
        $fillColor = '';
        if ($request->category == 'Kerusakan') {
            $fillColor = '#E78413'; // Orange
        } elseif ($request->category == 'Penghijauan') {
            $fillColor = '#11D44C'; // Yellow
        }
    
        // Check if an image was uploaded
        if ($image) {
            // Get the image contents as a binary string
            $imageBinary = file_get_contents($image->getPathname());
    
            // Store the image in the database as a BLOB
            Reports::create([
                'report_title' => $request->report_title,
                'category' => $request->category,
                // 'location' => $request->location,
                'coordinates' => $request->coordinates,
                'fillColor' => $fillColor,
                'date' => $request->date,
                'description' => $request->description,
                'image' => $imageBinary, // Store the image as a BLOB
            ]);
        } else {
            Reports::create([
                'report_title' => $request->report_title,
                'category' => $request->category,
                // 'location' => $request->location,
                'coordinates' => $request->coordinates,
                'fillColor' => $fillColor,
                'date' => $request->date,
                'description' => $request->description,
            ]);
        }

        Session::flash('successes', 'Laporan berhasil ditambahkan.');
    
        return view('report.report');
    }

    public function map(Request $req) {
        $spot = new Spot();

        $spot->slug = Str::slug($req->name);
        $spot->coordinates = request('coordinates');
        $spot->fillColor = request('fillColor');
        $spot->save();
        $spot->getYear()->sync($req->year_id);  

        return redirect('/report')->with('msg', 'Location has been added!');
    }

    public function show($id)
    {
        $report = Reports::findOrFail($id);
        return view('report.viewdetail', compact('report'));
    }


    public function retrieveImage($reportId)
    {
        $report = Reports::findOrFail($reportId);

        // Check if the report has an image
        if (!$report->image) {
            abort(404);
        }

        // Return the image as a response
        return response($report->image)
            ->header('Content-Type', 'image/jpeg'); // Adjust content type if necessary
    }

    public function destroy($id)
    {
        $report = Reports::findOrFail($id);
        $reportTitle = $report->report_title;
        $report->delete();

        return redirect('/viewreport')->with('success', 'Laporan "' . $reportTitle . '" berhasil dihapus.');
    }   

    public function locations() {
        $kerusakanReports = Reports::where('category', 'Kerusakan')->get();
        $penghijauanReports = Reports::where('category', 'Penghijauan')->get();
        // $spots = Reports::all(); // Ubah $spot menjadi $spots
        $spotCoordinates = Reports::all()->pluck('coordinates'); // Ubah $spotCoords menjadi $spotCoordinates
        // $years = Year::get(); // Hapus komentar jika akan digunakan nanti
    
        if(Auth::id()) {
            $role = Auth()->user()->role;
    
            if($role == 'User' || $role == 'Pemda') { // Gabungkan kondisi Admin dan Pemda karena keduanya menggunakan halaman yang sama
                return view('report.viewmaps', [
                    'kerusakan' => $kerusakanReports,
                    'penghijauan' => $penghijauanReports,
                    'spotCoordinates' => json_encode($spotCoordinates),
                    // 'years' => $years,
                ]);
            }
            else {
                return redirect()->back();
            }
        }
    }
}
