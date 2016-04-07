<?php


namespace Tests\App\Exceptions;


use App\Exceptions\Handler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TestCase;
use Mockery as m;

class HandlerTest extends TestCase
{
    public function testRespondsWithHtmlWhenJsonNotAccepted()
    {
        //make the mock partial, we only want to mock the 'isDebugMode' method
        $subject = m::mock(Handler::class)->makePartial();
        $subject->shouldNotReceive('isDebugMode');

        //Mock the interaction with the Request
        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(FALSE);

        //Mock the interaction with the Exception
        $exception = m::mock(\Exception::class, ['Error!']);
        $exception->shouldNotReceive('getStatusCode');
        $exception->shouldNotReceive('getTrace');
        $exception->shouldNotReceive('getMessage');

        //Call the method under test, this is not a mocked method
        $result = $subject->render($request, $exception);

        //Assert that 'render' does not return a Json Response
        $this->assertNotInstanceOf(JsonResponse::class, $result);
    }

    public function testRespondsWithJsonForJsonConsumers()
    {
        $subject = m::mock(Handler::class)->makePartial();
        $subject->shouldReceive('isDebugMode')->andReturn(FALSE);

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(TRUE);

        $exception = m::mock(\Exception::class, ['Doh!']);
        $exception->shouldReceive('getMessage')->andReturn('Doh!');

        $result = $subject->render($request, $exception);
        $data = $result->getData();

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertObjectHasAttribute('error', $data);
        $this->assertAttributeEquals('Doh!', 'message', $data->error);
        $this->assertAttributeEquals(400, 'status', $data->error);
    }

    public function testProvidesJsonResponsesForHttpExceptions()
    {
        $subject = m::mock(Handler::class)->makePartial();
        $subject->shouldReceive('isDebugMode')->andReturn(FALSE);

        $request = m::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(TRUE);

        $examples = [
            [
                'mock'    => NotFoundHttpException::class,
                'status'  => 404,
                'message' => 'Not Found'
            ],
            [
                'mock'    => AccessDeniedHttpException::class,
                'status'  => 403,
                'message' => 'Forbidden'
            ],
            [
                'mock'    => ModelNotFoundException::class,
                'status'  => 404,
                'message' => 'Not Found'
            ]
        ];

        foreach ($examples as $example) {
            $exception = m::mock($example['mock']);
            $exception->shouldReceive('getMessage')->andReturn(NULL);
            $exception->shouldReceive('getStatusCode')->andReturn($example['status']);

            $result = $subject->render($request, $exception);
            $data = $result->getData();

            $this->assertEquals($example['status'], $result->getStatusCode());
            $this->assertEquals($example['message'], $data->error->message);
            $this->assertEquals($example['status'], $data->error->status);
        }
    }
}