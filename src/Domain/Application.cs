using System.Collections.Generic;

namespace Domain
{
    public class Application
    {
        public long Id { get; set; }
        public string Name { get; set; }
        public List<long> EnvironmentIds { get; set; }
        
        public Application()
        {
            EnvironmentIds = new List<long>();
        }
    }
}