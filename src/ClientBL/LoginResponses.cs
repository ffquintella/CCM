using System;
namespace ClientBL
{
    public enum LoginResponses
    {   
        UserNotFound, 
        OK, 
        Unauthorized,
        ServerNotFound,
        NetworkError,
        Error
    }
}
