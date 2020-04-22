using System;
namespace ClientBL
{
    public class Configurations
    {

        public string CCMServer { get; set; }
        public string BaseURL { get; set; }
        public string CCMAPIVersion { get; set; }
        public short AppKeySize { get; set; }

        #region CONSTS

        public int HttpTimeout { get; } = 60000;

        #if DEBUG
            public bool UseDebugProxy { get; } = true; 
        #else
            public bool UseDebugProxy { get; } = false;
        #endif


        public string DebugProxy { get; } = "http://127.0.0.1:8080";

        #endregion


        private static readonly Configurations instance = new Configurations();

        private Configurations()
        {
            CCMServer = "";
        }

        public static Configurations Instance
        {
            get
            {
                return instance;
            }
        }
    }
}
