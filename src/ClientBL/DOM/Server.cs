using System;
using System.Collections.Generic;

namespace ClientBL.DOM
{
    public class Server
    {
        public String Name { get; set; }
        public String FQDN { get; set; }

        public Dictionary<String, List<String>> Assignments { get; set; }

    }
}
