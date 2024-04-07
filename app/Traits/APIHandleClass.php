<?php
namespace  App\Traits;

trait APIHandleClass
{
    protected $data;
    protected $message;
    protected  $statusCode = 200;
    protected $statusMessage = true;




    /**
     * Sets the data for the API response.
     *
     * @param mixed $data The data to be set.
     * @return void
     */
    function setData($data){
        // Sets the data for the API response.
        //
        // Parameters:
        // - $data (mixed): The data to be set.
        //
        // Returns:
        // This function does not return anything.
        $this->data = $data;
    }


    /**
     * Set the message for the API response.
     *
     * @param string $message The message to be set.
     * @return void
     */
    function setMessage(string $message) {
        // Sets the message for the API response.
        //
        // Parameters:
        // - $message (string): The message to be set.
        //
        // Returns:
        // This function does not return anything.
        $this->message = $message;
    }
    /**
     * Sets the HTTP status code for the API response.
     *
     * @param int $statusCode The HTTP status code to be set.
     * @return void
     */
    function setStatusCode(int $statusCode) {
        // Sets the HTTP status code for the API response.
        //
        // Parameters:
        // - $statusCode (int): The HTTP status code to be set.
        //
        // Returns:
        // This function does not return anything.
        $this->statusCode = $statusCode;
    }
    /**
     * Sets the status message for the API response.
     *
     * @param bool $statusMessage The status message to be set.
     *
     * This function sets the status message for the API response.
     * It takes a boolean parameter, $statusMessage, which represents
     * the status message to be set. The status message is a boolean
     * value indicating the success or failure of the API response.
     *
     * @return void
     */
    function setStatusMessage(bool $statusMessage) {
        // Sets the status message for the API response.
        //
        // Parameters:
        // - $statusMessage (bool): The status message to be set.
        //
        // Returns:
        // This function does not return anything.
        $this->statusMessage = $statusMessage;
    }

    /**
     * Returns the API response with the set data, message and status code.
     *
     * This function checks if the data and message properties are set,
     * if so, it returns a JSON response with the data, message and status code.
     * If only the message property is set, it returns a JSON response with the
     * message and status code. If neither data nor message is set, it returns
     * a JSON response with a status of false and an error message.
     *
     * @return \Illuminate\Http\JsonResponse The JSON API response.
     */
    function returnResponse() {
        // Check if data and message properties are set
        if(isset($this->data) && isset($this->message)){
            // Return JSON response with data, message and status code
            return response()->json([
                'status'=>$this->statusMessage,
                'message'=>$this->message,
                'data'=> $this->data
            ],$this->statusCode);
        }elseif(isset($this->message)){
            // Return JSON response with message and status code
            return response()->json([
                'status'=>$this->statusMessage,
                'message'=>$this->message
            ],$this->statusCode);

        }elseif(isset($this->data)){
            return response()->json([
                'status'=>$this->statusMessage,
                'data'=>$this->data
            ],$this->statusCode);

        }else{
            // Return JSON response with error message
            return response()->json([
                'status'=>false,
                'message'=>'Error!'
            ]);
        }
    }




}
