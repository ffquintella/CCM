using System.IO;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Domain.Protocol;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.StaticFiles;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;

namespace CCM_API.Controllers
{
    [ApiController]
    [ApiVersion("1")] 
    [Authorize(Policy = "DataManagement")]
    [Route("api/v{version:apiVersion}/[controller]")]
    [Produces("application/json")]
    public class DataController : BaseController<DataController>
    {
        public DataController(ILogger<DataController> logger,  
            IConfiguration configuration, 
            IgniteManager igniteManager,
            IHttpContextAccessor httpContextAccessor,
            DataManager dataManager
            ):
            base(logger,configuration,igniteManager, httpContextAccessor)
        {
            this.dataManager = dataManager;
            ControllerName = "DataController";
        }

        private DataManager dataManager;
        
        // GET
        [HttpGet]
        public string[] Get()
        {
            return new [] {"Export", "Import", "Download"} ;
        }
        
        // GET Download
        [AllowAnonymous]
        [HttpGet("Download/{year}/{month}/{fileName}")]
        public async Task<IActionResult> Download(int year, int month, string fileName)
        {
            if (fileName == null)
                return BadRequest("File not present"); 
  
            var path = Path.Combine(  
                Directory.GetCurrentDirectory(),  
                "fileStorage", year.ToString(), month.ToString(), fileName);

            if (!System.IO.File.Exists(path))
            {
                return NotFound();
            }
            
            var provider = new FileExtensionContentTypeProvider();
            string contentType;
            if(!provider.TryGetContentType(fileName, out contentType))
            {
                contentType = "application/octet-stream";
            }
            
            var memory = new MemoryStream();  
            using (var stream = new FileStream(path, FileMode.Open))  
            {  
                await stream.CopyToAsync(memory);  
            }  
            memory.Position = 0;  
            return File(memory, contentType, Path.GetFileName(path));
        } 
        
        // POST Export
        [HttpPost("Export")]
        public ActionResult<DataExportResult>  GetData([FromBody] DataExportResquest request)
        {
            
            string url = string.Concat(this.Request.Scheme, "://", this.Request.Host, "/api/v1/Data/Download" );

            var dataResult = dataManager.CreateDataFileV1(request.Encrypt, request.Password);

            if (dataResult.Item1) 
            {

                var fileName = dataResult.Item2;
                var result = new DataExportResult()
                {
                    FileVersion = 1,
                    FileLink = url + fileName,
                    Status = DataExportStatus.Ok

                };

                return result;
            }

            return StatusCode(500);

        }
    }
}