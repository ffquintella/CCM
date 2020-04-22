using System;
using ClientBL.DOM;

namespace ClientBL
{
    public class Session
    {

        #region SINGLETON
        private static readonly Session instance = new Session();

        private Session()
        {
        }

        public static Session Instance
        {
            get
            {
                return instance;
            }
        }
        #endregion

        public LoggedUser loggedUser { get; set; }

        public bool IsLoggedIn { get; set; } = false;

        public String LoggedServer { get; set; } = "";

    }
}
