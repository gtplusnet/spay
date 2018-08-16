import { TestBed, inject } from '@angular/core/testing';

import { GlobalConfigService } from './global-config.service';

describe('GlobalConfigService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [GlobalConfigService]
    });
  });

  it('should be created', inject([GlobalConfigService], (service: GlobalConfigService) => {
    expect(service).toBeTruthy();
  }));
});
