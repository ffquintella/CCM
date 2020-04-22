using System;
using System.Collections.Generic;

namespace ClientBL.DOM
{
    public class Credential
    {
        public String Name { get; set; }
        public String App { get; set; }

        public String Type { get; set; }

        public Dictionary<String, String> Values { get; set; }

        public Dictionary<String, String> VaultIds { get; set; }
    }
}
