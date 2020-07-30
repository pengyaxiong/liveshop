<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Live\V20180801\LiveClient;
use TencentCloud\Live\V20180801\Models\DescribeLiveRecordTemplatesRequest;
use TencentCloud\Live\V20180801\Models\CreateLiveRecordTemplateRequest;
use TencentCloud\Live\V20180801\Models\DeleteLiveRecordTemplateRequest;
use TencentCloud\Live\V20180801\Models\ModifyLiveRecordTemplateRequest;
use TencentCloud\Live\V20180801\Models\CreateRecordTaskRequest;
use TencentCloud\Live\V20180801\Models\StopRecordTaskRequest;
use TencentCloud\Live\V20180801\Models\DeleteRecordTaskRequest;

class LiveController extends Controller
{
    protected $SecretId;
    protected $SecretKey;

    public function __construct()
    {
        $this->SecretId = env('SecretId');
        $this->SecretKey = env('SecretKey');
    }

    public function DescribeLiveRecordTemplates(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new DescribeLiveRecordTemplatesRequest();

            $params = array(
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->DescribeLiveRecordTemplates($req);

            print_r($resp->toJsonString());
        }
        catch(TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function CreateLiveRecordTemplate(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "ap-beijing", $clientProfile);

            $req = new CreateLiveRecordTemplateRequest();

            $params = array(
                "TemplateName" => $request->TemplateName,
                "Description" => $request->Description,
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->CreateLiveRecordTemplate($req);

            //TemplateId  RequestId
            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function ModifyLiveRecordTemplate(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new ModifyLiveRecordTemplateRequest();

            $params = array(
                "TemplateId" => $request->TemplateId,
                "TemplateName" => $request->TemplateName,
                "Description" => $request->Description,
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->ModifyLiveRecordTemplate($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function DeleteLiveRecordTemplate(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new DeleteLiveRecordTemplateRequest();

            $params = array(
                "TemplateId" => $request->TemplateId
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->DeleteLiveRecordTemplate($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function CreateRecordTask(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "ap-guangzhou", $clientProfile);

            $req = new CreateRecordTaskRequest();

            $params = array(
                "StreamName" => $request->StreamName,          //!流名称。
                "DomainName" => $request->DomainName,            //!推流域名。
                "AppName" => $request->AppName,               //!推流路径。
                "StartTime" => $request->StartTime,               //录制任务开始时间，Unix时间戳。如果不填表示立即启动录制。不超过从当前时间开始24小时之内的时间。
                "EndTime" => $request->EndTime,                 //!录制任务结束时间，Unix时间戳。设置时间必须大于StartTime，且不能超过从当前时刻开始24小时之内的时间。
                "StreamType" => $request->StreamType,              //推流类型，默认0。取值： 0-直播推流。 1-合成流，即 A+B=C 类型混流。
                "TemplateId" => $request->TemplateId,              //录制模板ID，CreateLiveRecordTemplate 返回值。如果不填或者传入错误ID，则默认录制HLS格式、永久存储。
                "Extension" => $request->Extension             //扩展字段，默认空。
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->CreateRecordTask($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function StopRecordTask(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new StopRecordTaskRequest();

            $params = array(
                "TaskId" => $request->TaskId
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->StopRecordTask($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }

    public function DeleteRecordTask(Request $request)
    {
        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);

            $req = new DeleteRecordTaskRequest();

            $params = array(
                "TaskId" => $request->TaskId
            );
            $req->fromJsonString(json_encode($params));


            $resp = $client->DeleteRecordTask($req);

            print_r($resp->toJsonString());
        } catch (TencentCloudSDKException $e) {
            echo $e;
        }
    }
}
