using System;

namespace Domain.Exceptions
{
    public class ParameterUnavaliableException: Exception
    {
        public string Details { get; set; }
        public ParameterUnavaliableException(string details)
        {
            Details = details;
        }
    }
}