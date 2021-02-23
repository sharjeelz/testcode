<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($user_id = $request->get('user_id')) {

            $response = $this->repository->getUsersJobs($user_id);
        } elseif (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()) {
            $response = $this->repository->getAll($request);
        }
        /** Better to have response code along the response */
        return response($response, 200);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {

        try {

            $job = $this->repository->with('translatorJobRel.user')->findOrFail($id);
            $response = array("data" => $job, "message" => "Job Found", "status" => 200);
        } catch (\Exception $e) {
            $response = array("data" => null, "message" => "Job Not Found", "status" => 404);
        }
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(StoreDataRequest $request)
    {
        $validated_data = $request->validated();
        $response = $this->repository->store(Auth::user(), $validated_data);

        $response = array("data" => null, "message" => "Data saved", "status" => 200);
        return response()->json($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, StoreDataRequest $request)
    {
        $validated_data = $request->validated();
        $response = $this->repository->updateJob($id, $validated_data, Auth::user());
        $response = array("data" => null, "message" => "Data updated", "status" => 200);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {

        $data = $request->all();

        $response = $this->repository->storeJobEmail($data);
        $response = array("data" => null, "message" => "ok", "status" => 200);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {

            $data = $this->repository->getUsersJobsHistory($user_id, $request);
            $response = array("data" => $data, "message" => "ok", "status" => 200);
        } else {
            $response = array("data" => null, "message" => "ok", "status" => 404);
        }
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->acceptJob($data, Auth::user());
        $response = array("data" => null, "message" => "ok", "status" => 200);
        return response()->json($response);
    }

    public function acceptJobWithId(Request $request)
    {
        if ($job_id = $request->get('job_id')) {

            $response = $this->repository->acceptJobWithId($job_id, Auth::user());
            $response = array("data" => null, "message" => "ok", "status" => 200);
        } else {
            $response = array("data" => null, "message" => "ok", "status" => 404);
        }

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->cancelJobAjax($data, Auth::user());
        $response = array("data" => null, "message" => "ok", "status" => 200);

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);
        $response = array("data" => null, "message" => "ok", "status" => 200);
        return response()->json($response);
    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);
        $response = array("data" => null, "message" => "ok", "status" => 200);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {

        $response = $this->repository->getPotentialJobs(Auth::user());
        $response = array("data" => null, "message" => "ok", "status" => 200);
        return response()->json($response);
    }

    public function distanceFeed(Request $request)
    {
        $distance = "";
        $time = "";
        $session = "";
        $admincomment = "";
        if ($request->has('distance') && $request->filled('distance')) {
            $distance = $request->input('distance');
        }
        if ($request->has('time') && $request->filled('time')) {
            $time = $request->input('time');
        }
        if ($request->has('jobid') && $request->filled('jobid')) {
            $jobid = $request->input('jobid');
        }

        if ($request->has('session_time') && $request->filled('session_time')) {
            $session = $request->input('session_time');
        }
        if ($request->input('flagged')) {
            if (empty($request->input('admincomment'))) {
                $response = array("data" => null, "message" => "Please, add comment", "status" => 200);
                return response()->json($response);
            }
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }

        if ($request->input('manually_handled')) {
            $manually_handled = 'yes';
        } else {
            $manually_handled = 'no';
        }

        if ($request->input('by_admin')) {
            $by_admin = 'yes';
        } else {
            $by_admin = 'no';
        }

        if ($request->has('admincomment') && $request->filled('admincomment')) {

            $admincomment = $request->input('admincomment');
        }
        if ($time || $distance) {
            $affectedRows = $this->repository->updateDistance($jobid, $distance, $time);
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            $affectedRows1 = $this->repository->updateAdminComments($jobid, array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));
        }
        $response = array("data" => null, "message" => "Record updated!", "status" => 204);
        return response()->json($response);
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);
        $response = array("data" => null, "message" => "ok", "status" => 200);
        return response()->json($response);
    }

    public function resendNotifications(Request $request)
    {
        try {
            $job = $this->repository->findOrFail($request->input('jobid'));
            $job_data = $this->repository->jobToData($job);
            try {
            $this->repository->sendNotificationTranslator($job, $job_data, '*');
            $response = array("data" => null, "message" => "Push sent", "status" => 200);
            }catch (\Exception $e) {
            
                $response = array("data" => null, "message" => "Push Sending Failed", "status" => 200);
            }
            
        } catch (\Exception $e) {
            $response = array("data" => null, "message" => "Job Not Found ", "status" => 404);
        }


        return response()->json($response);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->findOrFail($data['jobid']);
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            $response = array("data" => null, "message" => "SMS sent ", "status" => 200);

        } catch (\Exception $e) {
            $response = array("data" => null, "message" => "SMS sending Failed ", "status" => 500);

        }

        return response()->json($response);
    }
}
