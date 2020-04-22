using System;
namespace ClientBL.Exceptions
{
    public class HttpRequestFailedException: Exception
    {
        public HttpRequestFailedException(String message = ""): base (message)
        {
 
        }
    }
}
