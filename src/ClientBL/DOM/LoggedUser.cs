using System;
namespace ClientBL.DOM
{
    public class LoggedUser
    {
        public string userName { get; set; } = "";
        public string tokenType { get; set; }
        public string tokenValue { get; set; }
    }
}
