using System;

namespace CCM_API.Exceptions
{
    public class InvalidParametersException: Exception
    {
        public string Class { get; set; }
        public string Method { get; set; }
        
        public InvalidParametersException(string classV, string method)
        {
            Class = classV;
            Method = method;
        }
    }
}