<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\reports;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function create(): View
    {
        return view('report.report');
    }

    public function viewReport()
    {
        $reports = Reports::all();

        return view('report.viewreport', compact('reports'));
    }

    public function store(Request $request){
        $request->validate([ 
            'report_title' => 'required|string|max:255', 
            'category' => 'required|string|max:255', 
            'location' => 'required|string|max:255', 
            'date' => 'required|date', 
            'description' => 'required|string', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
        ]);
    
        // Get the uploaded image
        $image = $request->file('image');
    
        // Check if an image was uploaded
        if ($image) {
            // Get the image contents as a binary string
            $imageBinary = file_get_contents($image->getPathname());
    
            // Store the image in the database as a BLOB
            reports::create([
                'report_title' => $request->report_title,
                'category' => $request->category,
                'location' => $request->location,
                'date' => $request->date,
                'description' => $request->description,
                'image' => $imageBinary, // Store the image as a BLOB
            ]);
        } else {
            reports::create([
                'report_title' => $request->report_title,
                'category' => $request->category,
                'location' => $request->location,
                'date' => $request->date,
                'description' => $request->description,
            ]);
        }
    
        return view('report.report');
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
}