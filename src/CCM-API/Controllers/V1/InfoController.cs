using System.Linq;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using Serilog;
using Domain.Metadata;

namespace CCM_API.Controllers
{
    [ApiController]
    [ApiVersion("1")] 
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class InfoController : ControllerBase
    {
        public InfoController(ILogger<InfoController> logger,  IConfiguration configuration, IgniteManager igniteManager)
        {
            Configuration = configuration;
            _logger = logger;
            _igniteManager = igniteManager;
        }
        
        public IConfiguration Configuration { get; }
        private readonly ILogger<InfoController> _logger;
        private readonly IgniteManager _igniteManager;
        
        // GET
        [HttpGet]
        public string Get()
        {
            return "CCM API";
        }
        
        // GET
        [HttpGet("db")]
        public IgniteDBInfo GetDbInfo()
        {
            _logger.LogDebug("Getting DB Information");
            var ignition = _igniteManager.GetIgnition();

            var dbinfo = new IgniteDBInfo()
            {
                Version = string.Format("{0}.{1}.{2}", 
                    ignition.GetVersion().Major.ToString(), 
                    ignition.GetVersion().Minor.ToString(),
                    ignition.GetVersion().Maintenance.ToString()),
                Server = ignition.GetCluster().GetNode().HostNames.First()
                
            };

            return dbinfo;

        }
    }
}