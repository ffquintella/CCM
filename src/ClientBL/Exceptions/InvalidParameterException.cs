using System;
namespace ClientBL.Exceptions
{
    public class InvalidParameterException: Exception
    {
        public InvalidParameterException(String message = ""): base(message)
        {
        }
    }
}
