<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReportService;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $role = (int)$user->role;
        
        // Get user's activity summary
        $myActivity = $this->reportService->getMyActivitySummary();
        
        // Get historical data for line graphs (last 6 months)
        $historicalData = $this->reportService->getHistoricalActivityData(6);
        
        return view('admin.reports.index', compact('myActivity', 'role', 'historicalData'));
    }

    public function appointments(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $data = $this->reportService->getAppointmentStats($period, $startDate, $endDate);
        
        return view('admin.reports.appointments', compact('data', 'period'));
    }

    public function events(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $filterBy = $request->get('filter_by', 'all');
        
        $data = $this->reportService->getEventStats($period, $startDate, $endDate, $filterBy);
        
        return view('admin.reports.events', compact('data', 'period', 'filterBy'));
    }

    public function students(Request $request)
    {
        $user = auth()->user();
        $role = (int)$user->role;
        
        // Only admins and students can access
        if ($role !== 4 && $role !== 1) {
            abort(403, 'Access denied.');
        }
        
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $data = $this->reportService->getStudentStats($period, $startDate, $endDate);
        
        if (isset($data['error'])) {
            return redirect()->route('reports.index')->with('error', $data['error']);
        }
        
        return view('admin.reports.students', compact('data', 'period'));
    }

    public function scholars(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $data = $this->reportService->getScholarStats($period, $startDate, $endDate);
        
        return view('admin.reports.scholars', compact('data', 'period'));
    }

    public function suspensions(Request $request)
    {
        $user = auth()->user();
        $role = (int)$user->role;
        
        // Only admins can access
        if ($role !== 4) {
            abort(403, 'Access denied. This report is only available to administrators.');
        }
        
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $data = $this->reportService->getSuspensionStats($period, $startDate, $endDate);
        
        if (isset($data['error'])) {
            return redirect()->route('reports.index')->with('error', $data['error']);
        }
        
        return view('admin.reports.suspensions', compact('data', 'period'));
    }

    public function comprehensive(Request $request)
    {
        $user = auth()->user();
        $role = (int)$user->role;
        
        // Only admins can access
        if ($role !== 4) {
            abort(403, 'Access denied. This report is only available to administrators.');
        }
        
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $data = $this->reportService->getComprehensiveStats($period, $startDate, $endDate);
        
        if (isset($data['error'])) {
            return redirect()->route('reports.index')->with('error', $data['error']);
        }
        
        return view('admin.reports.comprehensive', compact('data', 'period'));
    }

    public function myActivity(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $data = $this->reportService->getMyActivitySummary($period, $startDate, $endDate);
        
        return view('admin.reports.my-activity', compact('data', 'period'));
    }
}
